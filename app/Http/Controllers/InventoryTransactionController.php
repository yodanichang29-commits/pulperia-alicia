<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Provider;
use App\Models\InventoryMovement;
use App\Models\InventoryTransaction;
use App\Models\PurchasePayment;
use App\Models\CashMovement;
use App\Models\CashShift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryTransactionController extends Controller
{
    /**
     * LISTADO (Ingresos y Egresos)
     */
  public function index(Request $request)
{
    $from = $request->get('from');
    $to = $request->get('to');
    $type = $request->get('type');
    $providerId = $request->get('provider_id'); // <-- filtro nuevo

    // Consulta principal con eager loading para evitar N+1
    $query = \App\Models\InventoryTransaction::query()
        ->with(['user', 'provider', 'items.product'])
        ->when($from, fn($q) => $q->whereDate('moved_at', '>=', $from))
        ->when($to, fn($q) => $q->whereDate('moved_at', '<=', $to))
        ->when($type, fn($q) => $q->where('type', $type))
        ->when($providerId, fn($q) => $q->where('provider_id', $providerId))
        ->orderByDesc('moved_at');

    $txs = $query->paginate(15)->withQueryString();

    // Proveedores para el combo
    $providers = \App\Models\Provider::orderBy('name')->pluck('name', 'id');

    return view('inventario.movimientos.index', [
        'txs' => $txs,
        'from' => $from,
        'to' => $to,
        'type' => $type,
        'providers' => $providers,   // 👈 aquí lo mandas a la vista
        'providerId' => $providerId, // 👈 para mantener seleccionado el filtro
    ]);
}


    /**
     * FORMULARIO "Nuevo movimiento"
     */
    public function create()
    {
        return view('inventario.movimientos.create');
    }

    /**
     * GUARDAR movimiento (encabezado + filas)
     */
    public function store(Request $request)
    {
        // 1) Validación
        $data = $request->validate([
            'type'         => 'required|in:in,out',
            'reason'       => 'required|in:purchase,adjust_in,waste,damaged,expired,internal_use,adjust_out',
            'moved_at'     => 'nullable|date',
            'provider_id'  => 'nullable|exists:providers,id',
            'reference'    => 'nullable|string|max:255',
            'notes'        => 'nullable|string|max:2000',

            'items'                 => 'required|array|min:1',
            'items.*.product_id'    => 'required|exists:products,id',
            'items.*.qty'           => 'required|integer|min:1',
            'items.*.unit_cost'     => 'nullable|numeric|min:0',

            // Validación de pagos (solo para compras)
            'payments'              => 'nullable|array|min:1',
            'payments.*.method'     => 'nullable|in:caja,externo,credito,transferencia,tarjeta',
            'payments.*.amount'     => 'nullable|numeric|min:0',
            'payments.*.affects_cash' => 'nullable|boolean',
            'payments.*.notes'      => 'nullable|string|max:500',
        ]);

        // 2) Reglas extra
        if ($data['type'] === 'in' && $data['reason'] === 'purchase' && empty($data['provider_id'])) {
            return back()->withErrors(['provider_id' => 'Debes seleccionar un proveedor para una compra.'])->withInput();
        }

        // Validar pagos para compras
        $isPurchase = ($data['type'] === 'in' && $data['reason'] === 'purchase');
        if ($isPurchase && empty($data['payments'])) {
            return back()->withErrors(['payments' => 'Debes especificar al menos un método de pago para la compra.'])->withInput();
        }

        // 3) Ejecutar todo en transacción
        DB::beginTransaction();

        try {
            // Encabezado
            $tx = InventoryTransaction::create([
                'type'        => $data['type'],
                'reason'      => $data['reason'],
                'moved_at'    => $data['moved_at'] ?? now(),
                'provider_id' => $data['provider_id'] ?? null,
                'reference'   => $data['reference'] ?? null,
                'notes'       => $data['notes'] ?? null,
                'user_id'     => Auth::id(),
                'total_cost'  => 0,
            ]);

            $total = 0;

            // Nombre del proveedor (texto) opcional para snapshot en movimientos
            $supplierName = null;
            if (!empty($data['provider_id'])) {
                $supplierName = optional(Provider::find($data['provider_id']))->name;
            }

            // Detalle
            foreach ($data['items'] as $row) {
                $product = Product::findOrFail($row['product_id']);

                // Validar que el producto pertenezca al proveedor seleccionado (si aplica)
                if (!empty($data['provider_id']) && (int)$product->provider_id !== (int)$data['provider_id']) {
                    throw new \RuntimeException("El producto {$product->name} no pertenece al proveedor seleccionado.");
                }

                // Cantidad y costo unitario (fallback a purchase_price)
                $qty  = (int) $row['qty'];
                $cost = (float) ($row['unit_cost'] ?? ($product->purchase_price ?? 0));

                // Stock antes/después
                $qty    = (int) $row['qty'];
    $cost   = (float) ($row['unit_cost'] ?? $product->purchase_price ?? 0);
    $before = (int) $product->stock;
    $after  = $data['type'] === 'in' ? $before + $qty : max($before - $qty, 0);

                // Crear MOVIMIENTO individual (tabla inventory_movements)
                InventoryMovement::create([
                    'transaction_id' => $tx->id,             // si tu columna existe (nullable)
                    'product_id'     => $product->id,
                    'type'           => $data['type'],       // <= IMPRESCINDIBLE (era tu error)
                    'qty'            => $qty,
                    'before_qty'     => $before,
                    'after_qty'      => $after,
                    'reason'         => $data['reason'],
                    'user_id'        => Auth::id(),
                    'moved_at'       => $data['moved_at'] ?? now(),
                    'supplier'       => $supplierName,       // snapshot texto
                    'reference'      => $data['reference'] ?? null,
                    'unit_cost'      => $cost,
                    'total_cost'     => $qty * $cost,
                ]);

                // Actualizar stock del producto
               if ($data['type'] === 'in') {
        $product->increment('stock', $qty);
    } else {
        $product->decrement('stock', $qty);
    }

    $total += $qty * $cost;
            }

            // Total en encabezado
            $tx->update(['total_cost' => $total]);

            // ===== PROCESAR PAGOS (solo para compras) =====
            if ($isPurchase && !empty($data['payments'])) {
                // Calcular suma de pagos
                $totalPayments = collect($data['payments'])->sum(function($p) {
                    return (float) ($p['amount'] ?? 0);
                });

                // Validar que los pagos sumen el total de la compra
                if (abs($totalPayments - $total) > 0.01) {
                    throw new \RuntimeException(
                        "Los pagos (L{$totalPayments}) no coinciden con el total de la compra (L{$total})"
                    );
                }

                // Obtener el turno abierto del usuario actual (si existe)
                $currentShift = CashShift::openForUser(Auth::id())->first();

                // Crear registros de pagos
                foreach ($data['payments'] as $payment) {
                    $amount = (float) ($payment['amount'] ?? 0);
                    if ($amount <= 0) continue; // Saltar pagos en cero

                    $affectsCash = (bool) ($payment['affects_cash'] ?? false);

                    // Crear el registro de pago
                    $purchasePayment = PurchasePayment::create([
                        'purchase_id'    => $tx->id,
                        'amount'         => $amount,
                        'payment_method' => $payment['method'],
                        'affects_cash'   => $affectsCash,
                        'notes'          => $payment['notes'] ?? null,
                        'user_id'        => Auth::id(),
                    ]);

                    // Si afecta la caja, crear movimiento de efectivo vinculado al turno
                    if ($affectsCash) {
                        CashMovement::create([
                            'cash_shift_id'  => $currentShift?->id, // Vincular al turno actual
                            'date'           => $data['moved_at'] ?? now(),
                            'type'           => 'egreso',
                            'category'       => 'pago_proveedor',
                            'description'    => "Pago compra #{$tx->id}" .
                                              (!empty($data['reference']) ? " (Ref: {$data['reference']})" : ""),
                            'amount'         => $amount,
                            'payment_method' => 'efectivo', // Pagos de caja son siempre efectivo
                            'notes'          => $payment['notes'] ?? null,
                            'created_by'     => Auth::id(),
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()
                ->route('ingresos.index')
                ->with('success', 'Movimiento registrado correctamente.');

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * DETALLE del documento (con items)
     */
  public function show(InventoryTransaction $transaction)
{
    // Carga los renglones + producto + usuario + proveedor + pagos
    $transaction->load(['items.product','user','provider','payments']);

    return view('inventario.movimientos.show', compact('transaction'));
}



public function void(InventoryTransaction $transaction)
{
    $updated = 0;

    DB::transaction(function () use ($transaction, &$updated) {
        // 1) Marcar como anulado de forma ATÓMICA (solo si aún no lo está)
        $updated = InventoryTransaction::where('id', $transaction->id)
            ->whereNull('voided_at')
            ->update([
                'voided_at' => now(),
                'voided_by' => auth()->id(),
            ]);

        // 2) Si no se marcó (ya estaba anulado), salir sin tocar stock
        if (!$updated) {
            return;
        }

        // 3) Revertir stock UNA SOLA VEZ
        $transaction->load('items.product');
        foreach ($transaction->items as $it) {
            $p = $it->product;
            if (!$p) continue;

            if ($transaction->type === 'in') {
                $p->decrement('stock', $it->qty);
            } else {
                $p->increment('stock', $it->qty);
            }
        }
    });

    if (!$updated) {
        return back()->with('info', 'Este movimiento ya estaba anulado.');
    }

    return back()->with('success', 'Movimiento anulado y stock revertido.');
}

}

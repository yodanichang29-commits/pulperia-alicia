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
->latest('moved_at');




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

            // 🔹 Campos de pago (solo relevantes para compras)
            'paid_from_cash'    => 'nullable|numeric|min:0',
            'paid_from_outside' => 'nullable|numeric|min:0',

        ]);

        // 2) Reglas extra
        if ($data['type'] === 'in' && $data['reason'] === 'purchase' && empty($data['provider_id'])) {
            return back()->withErrors(['provider_id' => 'Debes seleccionar un proveedor para una compra.'])->withInput();
        }

        // 🔹 Validar que los montos de pago sean consistentes (solo para compras)
        $paidFromCash = (float)($data['paid_from_cash'] ?? 0);
        $paidFromOutside = (float)($data['paid_from_outside'] ?? 0);

        // Validación de montos negativos (extra seguridad)
        if ($paidFromCash < 0 || $paidFromOutside < 0) {
            return back()->withErrors(['error' => 'Los montos de pago no pueden ser negativos.'])->withInput();
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
                // 🔹 Guardar montos de pago
                'paid_from_cash'    => $paidFromCash,
                'paid_from_outside' => $paidFromOutside,
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

            // 🔹 VALIDACIÓN: La suma de pagos no debe exceder el total de la compra
            $totalPagado = $paidFromCash + $paidFromOutside;
            if ($totalPagado > $total) {
                throw new \RuntimeException(
                    "El total pagado (L {$totalPagado}) no puede ser mayor al total de la compra (L {$total})."
                );
            }

            // 🔹 CREAR CASH_MOVEMENT si se pagó desde la caja del turno
            // Solo para compras (type=in, reason=purchase) y si paid_from_cash > 0
            if ($data['type'] === 'in' && $data['reason'] === 'purchase' && $paidFromCash > 0) {
                // Obtener el turno de caja ACTUAL del usuario
                $currentShift = CashShift::where('user_id', Auth::id())
                    ->whereNull('closed_at')
                    ->first();

                if (!$currentShift) {
                    throw new \RuntimeException(
                        'No hay turno de caja abierto. Debes abrir un turno antes de pagar desde la caja.'
                    );
                }

                // Crear el movimiento de caja automáticamente
                CashMovement::create([
                    'cash_shift_id'   => $currentShift->id,
                    'date'            => $data['moved_at'] ?? now()->toDateString(),
                    'type'            => 'egreso',
                    'category'        => 'pago_proveedor',  // 🔹 Categoría especial que se excluye de gastos operativos
                    'description'     => "Pago compra inventario (Ref: {$data['reference']}) - Proveedor: " .
                                        ($tx->provider->name ?? 'N/A'),
                    'amount'          => $paidFromCash,
                    'payment_method'  => 'efectivo',
                    'notes'           => "Pago automático desde caja. Compra ID: {$tx->id}",
                    'created_by'      => Auth::id(),
                ]);
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
    // Carga los renglones + producto + usuario + proveedor
    $transaction->load(['items.product','user','provider']);

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

<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\CashShift;
use App\Models\Client; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\InventoryMovement;


class CajaController extends Controller
{
    /** Vista principal de la caja. */
    public function index()
    {
      $products = Product::select('id','name','price','image_path')

            ->orderBy('name')
            ->get();

        $categories = Product::select('category')
            ->distinct()
            ->pluck('category')
            ->filter()
            ->values();

        return view('caja.index', compact('products','categories'));
    }

    /** Búsqueda por código de barras. */
    public function barcode(string $code)
    {
        $p = Product::where('barcode', $code)->first();

        if (!$p) {
            return response()->json(['message' => 'No existe'], 404);
        }

        return response()->json([
            'id'   => $p->id,
            'name' => $p->name,
            'price'=> (float) $p->price,
            'cat'  => $p->category,
        ]);
    }

    /** Registrar un cobro/venta. */
    public function charge(Request $request)
    {
        $userId = $request->user()->id;

        // 1) Validación
        $data = $request->validate([
            'items'           => 'required|array|min:1',
            'items.*.id'      => 'required|integer|exists:products,id',
            'items.*.qty'     => 'required|numeric|min:1',
            'items.*.price'   => 'required|numeric|min:0',

            'payment'         => 'required|in:cash,card,transfer,credit',
            'fee_pct'         => 'nullable|numeric|min:0',
            'surcharge'       => 'nullable|numeric|min:0',

            // efectivo
            'cash_received'   => 'nullable|numeric|min:0',
            'cash_change'     => 'nullable|numeric',

            // crédito
            'client_id'       => 'nullable|required_if:payment,credit|integer|exists:clients,id',
            'due_date'        => 'nullable|required_if:payment,credit|date',
        ]);

        $payment = $data['payment'];

        // 2) Turno OBLIGATORIO para cualquier venta
        $shift = CashShift::openForUser($userId)->first();
        if (!$shift) {
            return response()->json([
                'message' => 'Debes abrir un turno antes de registrar ventas.',
            ], 422);
        }
        $cashShiftId = $shift->id;

        // 3) Totales
        $subtotal  = collect($data['items'])
            ->reduce(fn($a,$it) => $a + ((float)$it['price'] * (float)$it['qty']), 0.0);

        $feePct    = (float)($data['fee_pct'] ?? 0);
        $surcharge = (float)($data['surcharge'] ?? 0);
        $total     = round($subtotal + $surcharge, 2);

        try {
            // 4) Persistencia
           $saleId = DB::transaction(function () use ($data, $userId, $payment, $cashShiftId, $feePct, $surcharge, $subtotal, $total) {

    // --- 4.1) PRE-CHEQUEO + BLOQUEO de productos (evita sobreventa) ---
    $prepared = []; // guardará productos bloqueados y cálculos por línea

    foreach ($data['items'] as $it) {
        // Bloquea el producto para esta transacción
        $p = Product::whereKey($it['id'])->lockForUpdate()->first();

        $qty   = (float)$it['qty'];     // viene con 12,3 en sale_items
        $price = (float)$it['price'];

        if ($qty <= 0)   abort(422, "Cantidad inválida para {$p->name}.");
        if ($price <= 0) abort(422, "Precio inválido para {$p->name}.");



        // ✅ VALIDACIÓN DE FECHA DE VENCIMIENTO
       // ✅ VALIDACIÓN DE FECHA DE VENCIMIENTO
        if ($p->expires_at) {
            $today = now()->startOfDay();
            $expiresAt = \Carbon\Carbon::parse($p->expires_at)->startOfDay();
            
            if ($expiresAt->lessThan($today)) {
                abort(422, "PRODUCTO_VENCIDO|{$p->name}|{$expiresAt->format('d/m/Y')}");
            }
        }

        // STOCK entero (unidades). Si quisieras permitir decimales, quita el ceil().
        $qtyUnits = (int)ceil($qty);

        if ($p->stock < $qtyUnits) {
            abort(422, "Stock insuficiente para {$p->name} (stock: {$p->stock}, pedido: {$qtyUnits}).");
        }

        $lineTotal = round($qty * $price, 2);

        $prepared[] = [
            'product'    => $p,
            'qty'        => $qty,        // se guarda en sale_items tal cual (puede tener 0.001)
            'qty_units'  => $qtyUnits,   // para stock/movimiento (entero)
            'price'      => $price,
            'line_total' => $lineTotal,
            'before'     => (int)$p->stock,
            'after'      => (int)($p->stock - $qtyUnits),
        ];
    }

    // --- 4.2) Crear la venta ---
    $sale = Sale::create([
        'user_id'       => $userId,
        'cash_shift_id' => $cashShiftId,
        'payment'       => $payment,
        'subtotal'      => $subtotal,
        'surcharge'     => $surcharge,
        'fee_pct'       => $feePct,
        'total'         => $total,
        // efectivo
        'cash_received' => $payment === 'cash' ? (float)($data['cash_received'] ?? 0) : null,
        'cash_change'   => $payment === 'cash' ? round((float)($data['cash_received'] ?? 0) - $total, 2) : null,
        // crédito
        'client_id'     => $payment === 'credit' ? ($data['client_id'] ?? null) : null,
        'due_date'      => $payment === 'credit' ? ($data['due_date'] ?? null) : null,
    ]);

    // --- 4.3) Items + Rebaja de stock + Movimiento por cada línea ---
    foreach ($prepared as $row) {
        // a) Detalle de venta
        SaleItem::create([
            'sale_id'    => $sale->id,
            'product_id' => $row['product']->id,
            'qty'        => $row['qty'],        // respeta tus 12,3 en sale_items
            'price'      => $row['price'],
            'total'      => $row['line_total'],
        ]);

        // b) Rebajar stock (entero)
        $row['product']->update(['stock' => $row['after']]);

        // c) Movimiento de inventario (VENTA = salida)
        InventoryMovement::create([
            'product_id' => $row['product']->id,
            'type'       => 'out',               // ventas son salidas
            'qty'        => $row['qty_units'],   // POSITIVA
            'before_qty' => $row['before'],
            'after_qty'  => $row['after'],
            'reason'     => 'Venta #' . $sale->id,
            'sale_id'    => $sale->id,
            'user_id'    => $userId,
            'moved_at'   => now()->toDateString(),
            // Costos los dejas null porque en tu esquema los usas para ENTRADAS
        ]);
    }

    return $sale->id;
});


            return response()->json(['ok' => true, 'sale_id' => $saleId], 201);

        } catch (\Throwable $e) {
            \Log::error('Charge failed', [
                'msg'  => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);
            return response()->json([
                'message' => 'No se pudo registrar la venta.',
            ], 500);
        }
    }

    /** Buscar clientes (POS) */
    public function clients(Request $request)
    {
        $q = trim((string)$request->query('q', ''));
        if ($q === '') {
            return response()->json([]);
        }

        $clients = Client::query()
            ->select('id','name','phone')
            ->where(function($w) use ($q){
                $w->where('name','like',"%{$q}%")
                  ->orWhere('phone','like',"%{$q}%");
            })
            ->orderBy('name')
            ->limit(20)
            ->get();

        return response()->json($clients);
    }

    /** Crear cliente rápido (POS) */
    public function storeClient(Request $request)
    {
        $data = $request->validate([
            'name'  => 'required|string|max:120',
            'phone' => 'nullable|string|max:30',
        ]);

        if (!empty($data['phone'])) {
            if ($existing = Client::where('phone', $data['phone'])->first()) {
                return response()->json($existing, 200);
            }
        }

        $client = Client::create($data);
        return response()->json($client, 201);
    }
}

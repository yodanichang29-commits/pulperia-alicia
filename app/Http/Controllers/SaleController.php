<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{




 public function void(Sale $sale)
    {
        DB::transaction(function () use ($sale) {

            $sale->load(['items']); // items con product_id, qty, price

            foreach ($sale->items as $item) {
                $product = Product::whereKey($item->product_id)->lockForUpdate()->first();

                $qtyUnits = (int)ceil((float)$item->qty); // reponemos en unidades
                $before   = (int)$product->stock;
                $after    = $before + $qtyUnits;

                // reponer stock
                $product->update(['stock' => $after]);

                // movimiento inverso (ENTRADA)
                InventoryMovement::create([
                    'product_id' => $product->id,
                    'type'       => 'in',
                    'qty'        => $qtyUnits,      // POSITIVA
                    'before_qty' => $before,
                    'after_qty'  => $after,
                    'reason'     => 'Anulación venta #' . $sale->id,
                    'sale_id'    => $sale->id,
                    'user_id'    => auth()->id(),
                    'moved_at'   => now()->toDateString(),
                ]);
            }

            // Si luego agregas columna status en sales, aquí la marcas 'void'
            // $sale->update(['status' => 'void']);
        });

        return back()->with('success', 'Venta anulada y stock restaurado.');
    }







    public function store(Request $r)
    {
        $data = $r->validate([
            'items'             => 'required|array|min:1',
            'items.*.id'        => 'required|exists:products,id',
            'items.*.qty'       => 'required|numeric|min:1',
            'items.*.price'     => 'required|numeric|min:0.01',

            // ¡Usa los mismos valores que tu enum en la BD!
            'payment_method'    => 'required|in:cash,card,transfer,credit',

            // comisión de tarjeta
            'fee_pct'           => 'nullable|numeric|min:0',      // % (info)
            'surcharge'         => 'nullable|numeric|min:0',      // monto (si no lo mandas, lo calculo)

            // crédito
            'client_id'         => 'nullable|exists:clients,id',
            'due_date'          => 'nullable|date',
        ]);

        // si es crédito, cliente es obligatorio
        if ($data['payment_method'] === 'credit' && empty($data['client_id'])) {
            return response()->json(['message' => 'En ventas a crédito debes seleccionar o crear un cliente.'], 422);
        }

        // totales
        $subtotal  = collect($data['items'])->sum(fn ($i) => (float)$i['qty'] * (float)$i['price']);
        $feePct    = (float)($data['fee_pct'] ?? 0);
        // si no mandan 'surcharge', lo calculo cuando es tarjeta
        $surcharge = isset($data['surcharge'])
            ? round((float)$data['surcharge'], 2)
            : (($data['payment_method'] === 'card' && $feePct > 0) ? round($subtotal * $feePct / 100, 2) : 0.0);
        $total = round($subtotal + $surcharge, 2);

        $saleId = null;

        DB::transaction(function () use ($data, $subtotal, $surcharge, $feePct, $total, &$saleId) {
            $isCredit = $data['payment_method'] === 'credit';

            // crea la venta
            $sale = Sale::create([
                'user_id'        => auth()->id(),
                'payment_method' => $data['payment_method'],
                'subtotal'       => $subtotal,
                'surcharge'      => $surcharge,
                'fee_pct'        => $feePct,
                'total'          => $total,

                // campos de crédito (asegúrate de tenerlos en la tabla)
                'client_id'      => $isCredit ? $data['client_id'] : null,
                'due_date'       => $isCredit ? ($data['due_date'] ?? now()->addDays(30)->toDateString()) : null,
                'balance'        => $isCredit ? $total : 0,
                'status'         => $isCredit ? 'open' : 'paid',
            ]);

            $saleId = $sale->id;

            // items + descuento de stock
            foreach ($data['items'] as $i) {
                $qty   = (float)$i['qty'];
                $price = (float)$i['price'];

                SaleItem::create([
                    'sale_id'    => $sale->id,
                    'product_id' => $i['id'],
                    'qty'        => $qty,
                    'price'      => $price,
                    'subtotal'   => round($qty * $price, 2),
                ]);

                Product::whereKey($i['id'])->decrement('stock', (int)$qty);
            }

            // pago inmediato si NO es crédito
            if (!$isCredit) {
                SalePayment::create([
                    'sale_id' => $sale->id,
                    'user_id' => auth()->id(),
                    'amount'  => $total,
                    'method'  => $data['payment_method'], // cash | card | transfer
                    'paid_at' => now(),
                ]);
            }
        });

        return response()->json(['ok' => true, 'sale_id' => $saleId]);
    }
}

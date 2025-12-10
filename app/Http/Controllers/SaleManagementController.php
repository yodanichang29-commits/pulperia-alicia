<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\CashShift;
use App\Models\InventoryMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\PendingSale;


class SaleManagementController extends Controller
{







      /**
     * ⏸️ Guardar venta en ESPERA
     */
    public function holdSale(Request $request)
    {
        $data = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id'    => 'required|integer|exists:products,id',
             'items.*.name'     => 'nullable|string|max:255',  // 👈 nuevo
    'items.*.category' => 'nullable',                 // 👈 nuevo
            'items.*.qty'   => 'required|numeric|min:0.01',
            'items.*.price' => 'required|numeric|min:0',
            'customer_name' => 'nullable|string|max:255',
            'notes'         => 'nullable|string|max:500',
        ]);

        $userId = $request->user()->id;

        // Calcular total del carrito
        $total = 0;
        foreach ($data['items'] as $item) {
            $total += $item['qty'] * $item['price'];
        }

        $pending = PendingSale::create([
            'user_id'       => $userId,
            'customer_name' => $data['customer_name'] ?? null,
            'notes'         => $data['notes'] ?? null,
            'items'         => $data['items'],
            'total'         => $total,
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Venta guardada en espera',
            'pending_sale_id' => $pending->id,
        ]);
    }

    /**
     * 📋 Listar ventas en ESPERA del usuario actual
     * (para el contador y el modal)
     */
    public function listPendingSales(Request $request)
    {
        $userId = $request->user()->id;

        $pending = PendingSale::where('user_id', $userId)
            ->latest()
            ->get()
            ->map(function ($sale) {
                return [
                    'id'            => $sale->id,
                    'customer_name' => $sale->customer_name,
                    'notes'         => $sale->notes,
                    'items'         => $sale->items ?? [],
                    'total'         => (float)$sale->total,
                    'created_at'    => $sale->created_at,
                ];
            });

        // Tu JS espera exactamente { pending_sales: [...] }
        return response()->json([
            'pending_sales' => $pending,
        ]);
    }

    /**
     * ▶️ Recuperar UNA venta en espera
     * (cuando le das "Continuar" en el modal)
     */
    public function retrievePendingSale(Request $request, $id)
    {
        $userId = $request->user()->id;

        $pending = PendingSale::where('user_id', $userId)->findOrFail($id);

        return response()->json([
            'ok' => true,
            'pending_sale' => [
                'id'            => $pending->id,
                'customer_name' => $pending->customer_name,
                'notes'         => $pending->notes,
                'items'         => $pending->items ?? [],
                'total'         => (float)$pending->total,
                'created_at'    => $pending->created_at,
            ],
        ]);
    }

    /**
     * 🗑️ Eliminar venta en espera
     */
    public function deletePendingSale(Request $request, $id)
    {
        $userId = $request->user()->id;

        $pending = PendingSale::where('user_id', $userId)->findOrFail($id);
        $pending->delete();

        return response()->json([
            'ok' => true,
            'message' => 'Venta en espera eliminada',
        ]);
    }

    /**
     * ✅ Marcar como completada (ahorita solo borra el registro)
     * La venta real la registra Caja cuando cobrás.
     */
    public function completePendingSale(Request $request, $id)
    {
        $userId = $request->user()->id;

        $pending = PendingSale::where('user_id', $userId)->findOrFail($id);
        $pending->delete();

        return response()->json([
            'ok' => true,
            'message' => 'Venta en espera completada',
        ]);
    }


    /**
     * 🔍 BUSCAR VENTAS DEL TURNO ACTUAL QUE CONTENGAN UN PRODUCTO ESPECÍFICO
     * 
     * Esta función busca todas las ventas del turno abierto que tienen el producto buscado
     */
    public function searchProductInCurrentShift(Request $request)
    {
        // Validar que nos envíen el ID del producto
        $data = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
        ]);
        
        $userId = $request->user()->id;
        
        // ✅ PASO 1: Obtener el turno abierto
        $shift = CashShift::openForUser($userId)->first();
        
        if (!$shift) {
            return response()->json([
                'ok' => false,
                'message' => 'No hay turno abierto',
                'sales' => []
            ], 422);
        }
        
        // ✅ PASO 2: Buscar ventas del turno que contengan este producto
        // Incluye tanto ventas COMPLETADAS como DEVUELTAS para ver el historial completo
        $salesWithProduct = DB::table('sales as s')
            ->join('sale_items as si', 'si.sale_id', '=', 's.id')
            ->join('products as p', 'p.id', '=', 'si.product_id')
            ->where('s.cash_shift_id', $shift->id)
            ->where('s.user_id', $userId)
            ->whereIn('s.status', [Sale::STATUS_COMPLETED, Sale::STATUS_RETURNED])
            ->where('si.product_id', $data['product_id'])
            ->selectRaw("
                s.id as sale_id,
                s.created_at,
                s.status,
                si.id as sale_item_id,
                si.qty,
                si.price,
                si.total,
                p.name as product_name
            ")
            ->orderBy('s.created_at', 'desc')
            ->get();
        
        // ✅ VALIDACIÓN: Si no se vendió en este turno, retornar error
        if ($salesWithProduct->isEmpty()) {
            return response()->json([
                'ok' => false,
                'message' => '⚠️ Este producto NO se ha vendido en este turno',
                'detail' => 'No puedes devolver un producto que no has vendido hoy',
                'sales' => []
            ], 200);
        }
        
        // ✅ PASO 3: Retornar las ventas encontradas
        return response()->json([
            'ok' => true,
            'sales' => $salesWithProduct,
            'product_id' => $data['product_id'],
            'shift_id' => $shift->id,
        ]);
    }

    /**
     * 💰 PROCESAR DEVOLUCIÓN DE UN PRODUCTO
     * 
     * Esta función procesa la devolución, actualiza inventario y registra todo
     */
    public function returnSaleItem(Request $request)
    {
        $data = $request->validate([
            'sale_id'           => 'required|integer|exists:sales,id',
            'sale_item_id'      => 'required|integer|exists:sale_items,id',
            'qty'               => 'required|numeric|min:0.01',
            'return_reason'     => 'required|string|max:500',
           'product_condition' => 'nullable',

        ]);
        
        $userId = $request->user()->id;
        
        // ✅ PASO 1: Verificar que hay turno abierto
        $shift = CashShift::openForUser($userId)->first();
        if (!$shift) {
            return response()->json([
                'ok' => false,
                'message' => 'Debes abrir un turno antes de procesar devoluciones.'
            ], 422);
        }
        
        // ✅ PASO 2: Obtener la venta original
        $sale = Sale::where('id', $data['sale_id'])
            ->where('cash_shift_id', $shift->id)
            ->first();
        
        if (!$sale) {
            return response()->json([
                'ok' => false,
                'message' => 'Esta venta no pertenece al turno actual'
            ], 422);
        }
        
        // ✅ PASO 3: Verificar que la venta esté completada
        if ($sale->status !== Sale::STATUS_COMPLETED) {
            return response()->json([
                'ok' => false,
                'message' => 'Esta venta ya fue anulada o devuelta'
            ], 422);
        }
        
        // ✅ PASO 4: Obtener el item específico de la venta
        $saleItem = SaleItem::where('id', $data['sale_item_id'])
            ->where('sale_id', $sale->id)
            ->first();
        
        if (!$saleItem) {
            return response()->json([
                'ok' => false,
                'message' => 'Producto no encontrado en esta venta'
            ], 422);
        }
        
        $qtyToReturn = (float)$data['qty'];
        
        // ✅ PASO 5: Verificar que no devuelvan más de lo comprado
        if ($qtyToReturn > $saleItem->qty) {
            return response()->json([
                'ok' => false,
                'message' => 'No puedes devolver más cantidad de la comprada'
            ], 422);
        }
        
       $isGoodCondition = true; // siempre vuelve al inventario

        
        try {
            // ✅ PASO 6: Procesar la devolución en una transacción
            $returnSaleId = DB::transaction(function () use (
                $sale, 
                $saleItem, 
                $qtyToReturn, 
                $data, 
                $userId, 
                $shift,
                $isGoodCondition
            ) {
                // Bloquear el producto para evitar problemas de concurrencia
                $product = Product::whereKey($saleItem->product_id)
                    ->lockForUpdate()
                    ->first();
                
                $qtyUnits = (int)ceil($qtyToReturn);
                $before   = (int)$product->stock;
                
                // ✅ LÓGICA DE INVENTARIO SEGÚN CONDICIÓN DEL PRODUCTO
                if ($isGoodCondition) {
                    // 🟢 PRODUCTO EN BUEN ESTADO → Regresa al inventario
                    $after = $before + $qtyUnits;
                    $movementType = 'in';
                    $movementReason = 'Devolución (buen estado) - Venta #' . $sale->id;
                } else {
                    // 🔴 PRODUCTO DAÑADO/MERMA → NO regresa al inventario
                    $after = $before;
                    $movementType = 'out';
                    $movementReason = 'Devolución MERMA (mal estado) - Venta #' . $sale->id;
                }
                
                $lineTotal = round($qtyToReturn * $saleItem->price, 2);
                
                // ✅ Crear venta de devolución (negativa)
                $returnSale = Sale::create([
                    'user_id'          => $userId,
                    'cash_shift_id'    => $shift->id,
                    'payment'          => $sale->payment,
                    'subtotal'         => -$lineTotal,
                    'surcharge'        => 0,
                    'fee_pct'          => 0,
                    'total'            => -$lineTotal,
                       'status'           => Sale::STATUS_RETURNED, // ← Verifica que diga RETURNED
                    'original_sale_id' => $sale->id,
'return_reason'    => $data['return_reason'] . ' | Devolución (BUEN ESTADO)',
                    'cash_received'    => null,
                    'cash_change'      => null,
                ]);
                
                // ✅ Crear item de devolución
                SaleItem::create([
                    'sale_id'    => $returnSale->id,
                    'product_id' => $product->id,
                    'qty'        => -$qtyToReturn,
                    'price'      => $saleItem->price,
                    'total'      => -$lineTotal,
                ]);
                
                // ✅ ACTUALIZAR STOCK (solo si está en buen estado)
                if ($isGoodCondition) {
                    $product->update(['stock' => $after]);
                }
                
                // ✅ REGISTRAR MOVIMIENTO DE INVENTARIO
                InventoryMovement::create([
                    'product_id' => $product->id,
                    'type'       => $movementType,
                    'qty'        => $qtyUnits,
                    'before_qty' => $before,
                    'after_qty'  => $after,
                    'reason'     => $movementReason,
                    'sale_id'    => $returnSale->id,
                    'user_id'    => $userId,
                    'moved_at'   => now()->toDateString(),
                ]);
                
                return $returnSale->id;
            });
            
            return response()->json([
                'ok' => true,
                'return_sale_id' => $returnSaleId,
                'message' => $isGoodCondition 
                    ? '✅ Devolución procesada. Producto regresó al inventario'
                    : '✅ Devolución procesada. Producto registrado como MERMA',
                'amount_returned' => $qtyToReturn * $saleItem->price,
                'returned_to_stock' => $isGoodCondition,
            ], 201);
            
        } catch (\Throwable $e) {
            \Log::error('Return sale item failed', [
                'msg'  => $e->getMessage(),
                'line' => $e->getLine(),
            ]);
            
            return response()->json([
                'ok' => false,
                'message' => 'Error al procesar devolución: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 🔍 SUGERIR PRODUCTOS (AUTOCOMPLETADO)
     * 
     * Esta función busca productos por nombre o código de barras
     */
   public function suggestProducts(Request $request)
{
    $q = trim((string)$request->query('q', ''));

    if ($q === '') {
        return response()->json([]);
    }

    // Buscar productos por nombre o código de barras
    $products = Product::query()
        ->where(function ($query) use ($q) {
            $query->where('name', 'like', "%{$q}%")
                  ->orWhere('barcode', 'like', "%{$q}%");
        })
        ->orderBy('name')
        ->limit(15)
        ->get(['id', 'name', 'price', 'barcode']);   // 👈 SOLO columnas reales

    // Armar respuesta para el frontend
    $data = $products->map(function ($p) {
        return [
            'id'       => $p->id,
            'name'     => $p->name,
            'price'    => (float) $p->price,
            'barcode'  => $p->barcode ?? '',
            // Si tienes relación category() en el modelo Product
            'category' => optional($p->category)->name,
            // Si tienes accessor getImageUrlAttribute()
            'image'    => $p->image_url ?? null,
        ];
    });

    return response()->json($data);
}

}
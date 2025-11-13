<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\PendingSale;
use App\Models\InventoryMovement;
use App\Models\CashShift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleManagementController extends Controller
{
    // ============================================
    // VENTAS EN ESPERA
    // ============================================
    
    /**
     * Guardar venta en espera (NO afecta inventario)
     */
    public function holdSale(Request $request)
    {
        $data = $request->validate([
            'items'         => 'required|array|min:1',
            'items.*.id'    => 'required|integer|exists:products,id',
            'items.*.qty'   => 'required|numeric|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'customer_name' => 'nullable|string|max:100',
            'notes'         => 'nullable|string|max:500',
        ]);
        
        $userId = $request->user()->id;
        
        // Obtener turno actual
        $shift = CashShift::openForUser($userId)->first();
        if (!$shift) {
            return response()->json([
                'message' => 'Debes abrir un turno antes de guardar ventas en espera.'
            ], 422);
        }
        
        // Calcular totales
        $subtotal = collect($data['items'])
            ->reduce(fn($a, $it) => $a + ((float)$it['price'] * (float)$it['qty']), 0.0);
        $total = round($subtotal, 2);
        
        // Guardar venta en espera
        $pendingSale = PendingSale::create([
            'user_id'       => $userId,
            'cash_shift_id' => $shift->id,
            'items'         => $data['items'],
            'customer_name' => $data['customer_name'] ?? null,
            'notes'         => $data['notes'] ?? null,
            'subtotal'      => $subtotal,
            'total'         => $total,
        ]);
        
        return response()->json([
            'ok' => true,
            'pending_sale_id' => $pendingSale->id,
            'message' => 'Venta guardada en espera'
        ], 201);
    }
    
    /**
     * Listar ventas en espera del turno actual
     */
    /**
 * Listar ventas en espera del turno actual
 */
public function listPendingSales(Request $request)
{
    $userId = $request->user()->id;
    $shift = CashShift::openForUser($userId)->first();
    
    if (!$shift) {
        return response()->json(['pending_sales' => []]);
    }
    
    $pendingSales = PendingSale::where('cash_shift_id', $shift->id)
        ->orderBy('created_at', 'desc')
        ->get();
    
    // Enriquecer cada venta en espera con los nombres de productos
    $pendingSales->transform(function ($sale) {
        // Obtener IDs de productos de los items
        $productIds = collect($sale->items)->pluck('id')->unique();
        
        // Buscar los productos en la base de datos
        $products = Product::whereIn('id', $productIds)
            ->get()
            ->keyBy('id');
        
        // Agregar el nombre del producto a cada item
        $sale->items = collect($sale->items)->map(function ($item) use ($products) {
            $product = $products->get($item['id']);
            $item['name'] = $product ? $product->name : 'Producto no encontrado';
            return $item;
        })->toArray();
        
        return $sale;
    });
    
    return response()->json(['pending_sales' => $pendingSales]);
}
    
    /**
     * Recuperar venta en espera (para continuar editando)
     */
  public function retrievePendingSale(Request $request, $id)
{
    $userId = $request->user()->id;

    // Trae la venta y elimínala en la misma transacción (efecto "pop")
    $payload = DB::transaction(function () use ($userId, $id) {
        $pendingSale = \App\Models\PendingSale::where('user_id', $userId)->lockForUpdate()->findOrFail($id);

        // Obtener IDs de productos
        $productIds = collect($pendingSale->items)->pluck('id')->unique();
        
        // Buscar los productos en la base de datos
        $products = Product::whereIn('id', $productIds)
            ->get()
            ->keyBy('id');
        
        // Enriquecer items con nombres de productos
        $enrichedItems = collect($pendingSale->items)->map(function ($item) use ($products) {
            $product = $products->get($item['id']);
            $item['name'] = $product ? $product->name : 'Producto no encontrado';
            return $item;
        })->toArray();

        // Copia los datos ANTES de borrar
        $data = [
            'id'            => $pendingSale->id,
            'items'         => $enrichedItems,
            'customer_name' => $pendingSale->customer_name,
            'notes'         => $pendingSale->notes,
            'subtotal'      => $pendingSale->subtotal,
            'total'         => $pendingSale->total,
            'created_at'    => $pendingSale->created_at,
        ];

        // Eliminar para que ya NO aparezca en la lista
        $pendingSale->delete();

        return $data;
    });

    return response()->json(['pending_sale' => $payload]);
}

    
    /**
     * Eliminar venta en espera (cancelar)
     */
    public function deletePendingSale($id, Request $request)
    {
        $userId = $request->user()->id;
        
        $pendingSale = PendingSale::where('user_id', $userId)
            ->findOrFail($id);
        
        $pendingSale->delete();
        
        return response()->json([
            'ok' => true,
            'message' => 'Venta en espera eliminada'
        ]);
    }
    
    /**
     * Completar venta en espera (convertir a venta real)
     */
    public function completePendingSale($id, Request $request)
    {
        $userId = $request->user()->id;
        
        // Validación del método de pago
        $data = $request->validate([
            'payment'       => 'required|in:cash,card,transfer,credit',
            'fee_pct'       => 'nullable|numeric|min:0',
            'surcharge'     => 'nullable|numeric|min:0',
            'cash_received' => 'nullable|numeric|min:0',
            'cash_change'   => 'nullable|numeric',
            'client_id'     => 'nullable|required_if:payment,credit|integer|exists:clients,id',
            'due_date'      => 'nullable|required_if:payment,credit|date',
        ]);
        
        // Obtener venta en espera
        $pendingSale = PendingSale::where('user_id', $userId)
            ->findOrFail($id);
        
        $payment = $data['payment'];
        
        // Turno OBLIGATORIO
        $shift = CashShift::openForUser($userId)->first();
        if (!$shift) {
            return response()->json([
                'message' => 'Debes abrir un turno antes de completar la venta.'
            ], 422);
        }
        $cashShiftId = $shift->id;
        
        // Totales
        $subtotal  = $pendingSale->subtotal;
        $feePct    = (float)($data['fee_pct'] ?? 0);
        $surcharge = (float)($data['surcharge'] ?? 0);
        $total     = round($subtotal + $surcharge, 2);
        
        try {
            $saleId = DB::transaction(function () use (
                $pendingSale, 
                $data, 
                $userId, 
                $payment, 
                $cashShiftId, 
                $feePct, 
                $surcharge, 
                $subtotal, 
                $total
            ) {
                // Pre-chequeo y bloqueo de productos
                $prepared = [];
                
                foreach ($pendingSale->items as $it) {
                    $p = Product::whereKey($it['id'])->lockForUpdate()->first();
                    
                    $qty   = (float)$it['qty'];
                    $price = (float)$it['price'];
                    
                    if ($qty <= 0)   abort(422, "Cantidad inválida para {$p->name}.");
                    if ($price <= 0) abort(422, "Precio inválido para {$p->name}.");
                    
                    $qtyUnits = (int)ceil($qty);
                    
                    if ($p->stock < $qtyUnits) {
                        abort(422, "Stock insuficiente para {$p->name} (stock: {$p->stock}, pedido: {$qtyUnits}).");
                    }
                    
                    $lineTotal = round($qty * $price, 2);
                    
                    $prepared[] = [
                        'product'    => $p,
                        'qty'        => $qty,
                        'qty_units'  => $qtyUnits,
                        'price'      => $price,
                        'line_total' => $lineTotal,
                        'before'     => (int)$p->stock,
                        'after'      => (int)($p->stock - $qtyUnits),
                    ];
                }
                
                // Crear la venta
                $sale = Sale::create([
                    'user_id'       => $userId,
                    'cash_shift_id' => $cashShiftId,
                    'payment'       => $payment,
                    'subtotal'      => $subtotal,
                    'surcharge'     => $surcharge,
                    'fee_pct'       => $feePct,
                    'total'         => $total,
                    'status'        => Sale::STATUS_COMPLETED,
                    'cash_received' => $payment === 'cash' ? (float)($data['cash_received'] ?? 0) : null,
                    'cash_change'   => $payment === 'cash' ? round((float)($data['cash_received'] ?? 0) - $total, 2) : null,
                    'client_id'     => $payment === 'credit' ? ($data['client_id'] ?? null) : null,
                    'due_date'      => $payment === 'credit' ? ($data['due_date'] ?? null) : null,
                ]);
                
                // Items + Rebaja de stock + Movimiento
                foreach ($prepared as $row) {
                    SaleItem::create([
                        'sale_id'    => $sale->id,
                        'product_id' => $row['product']->id,
                        'qty'        => $row['qty'],
                        'price'      => $row['price'],
                        'total'      => $row['line_total'],
                    ]);
                    
                    $row['product']->update(['stock' => $row['after']]);
                    
                    InventoryMovement::create([
                        'product_id' => $row['product']->id,
                        'type'       => 'out',
                        'qty'        => $row['qty_units'],
                        'before_qty' => $row['before'],
                        'after_qty'  => $row['after'],
                        'reason'     => 'Venta #' . $sale->id,
                        'sale_id'    => $sale->id,
                        'user_id'    => $userId,
                        'moved_at'   => now()->toDateString(),
                    ]);
                }
                
                // Eliminar la venta en espera
                $pendingSale->delete();
                
                return $sale->id;
            });
            
            return response()->json([
                'ok' => true, 
                'sale_id' => $saleId,
                'message' => 'Venta completada exitosamente'
            ], 201);
            
        } catch (\Throwable $e) {
            \Log::error('Complete pending sale failed', [
                'msg'  => $e->getMessage(),
                'line' => $e->getLine(),
            ]);
            
            return response()->json([
                'message' => 'No se pudo completar la venta: ' . $e->getMessage(),
            ], 500);
        }
    }










/**
     * Buscar ventas del turno actual que contengan un producto específico
     */
    public function searchProductInCurrentShift(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
        ]);
        
        $userId = $request->user()->id;
        
        // Obtener turno actual
        $shift = CashShift::openForUser($userId)->first();
        if (!$shift) {
            return response()->json([
                'message' => 'No hay turno abierto'
            ], 422);
        }
        
        // Buscar ventas del turno que contengan este producto
        $salesWithProduct = DB::table('sales as s')
            ->join('sale_items as si', 'si.sale_id', '=', 's.id')
            ->join('products as p', 'p.id', '=', 'si.product_id')
            ->where('s.cash_shift_id', $shift->id)
            ->where('s.status', Sale::STATUS_COMPLETED)
            ->where('si.product_id', $data['product_id'])
            ->selectRaw("
                s.id as sale_id,
                s.created_at,
                si.id as sale_item_id,
                si.qty,
                si.price,
                si.total,
                p.name as product_name
            ")
            ->orderBy('s.created_at', 'desc')
            ->get();
        
        // VALIDACIÓN: Si no se vendió hoy, retornar error
        if ($salesWithProduct->isEmpty()) {
            return response()->json([
                'ok' => false,
                'message' => '⚠️ Este producto NO se ha vendido en este turno',
                'detail' => 'No puedes devolver un producto que no has vendido hoy',
                'sales' => []
            ], 200);
        }
        
        return response()->json([
            'ok' => true,
            'sales' => $salesWithProduct,
            'product_id' => $data['product_id'],
        ]);
    }




    /**
     * Procesar devolución de un producto
     * CON VALIDACIÓN y MANEJO DE MERMA
     */
    public function returnSaleItem(Request $request)
    {
        $data = $request->validate([
            'sale_id'       => 'required|integer|exists:sales,id',
            'sale_item_id'  => 'required|integer|exists:sale_items,id',
            'qty'           => 'required|numeric|min:0.01',
            'return_reason' => 'required|string|max:500',
           'product_condition' => 'sometimes|string|in:good',
        ]);
        
        $userId = $request->user()->id;
        
        // Verificar turno abierto
        $shift = CashShift::openForUser($userId)->first();
        if (!$shift) {
            return response()->json([
                'message' => 'Debes abrir un turno antes de procesar devoluciones.'
            ], 422);
        }
        
        // Obtener la venta original
        $sale = Sale::where('id', $data['sale_id'])
            ->where('cash_shift_id', $shift->id)
            ->first();
        
        if (!$sale) {
            return response()->json([
                'message' => 'Esta venta no pertenece al turno actual'
            ], 422);
        }
        
        // Verificar que la venta esté completada
        if ($sale->status !== Sale::STATUS_COMPLETED) {
            return response()->json([
                'message' => 'Esta venta ya fue anulada o devuelta'
            ], 422);
        }
        
        // Obtener el item específico
        $saleItem = SaleItem::where('id', $data['sale_item_id'])
            ->where('sale_id', $sale->id)
            ->first();
        
        if (!$saleItem) {
            return response()->json([
                'message' => 'Producto no encontrado en esta venta'
            ], 422);
        }
        
        $qtyToReturn = (float)$data['qty'];
        
        // Verificar que no devuelvan más de lo comprado
        if ($qtyToReturn > $saleItem->qty) {
            return response()->json([
                'message' => 'No puedes devolver más cantidad de la comprada'
            ], 422);
        }
        
       // Siempre tratamos las devoluciones como "buen estado"
$isGoodCondition = true;
$data['product_condition'] = 'good';
        
        try {
            $returnSaleId = DB::transaction(function () use (
                $sale, 
                $saleItem, 
                $qtyToReturn, 
                $data, 
                $userId, 
                $shift,
                $isGoodCondition
            ) {
                // Bloquear producto
                $product = Product::whereKey($saleItem->product_id)
                    ->lockForUpdate()
                    ->first();
                
                $qtyUnits = (int)ceil($qtyToReturn);
                $before   = (int)$product->stock;
                
                // LÓGICA DE INVENTARIO SEGÚN CONDICIÓN
                if ($isGoodCondition) {
                    // PRODUCTO EN BUEN ESTADO → Regresa al inventario
                    $after = $before + $qtyUnits;
                    $movementType = 'in';
                    $movementReason = 'Devolución (buen estado) - Venta #' . $sale->id;
                } else {
                    // PRODUCTO DAÑADO/MERMA → NO regresa al inventario
                    $after = $before;
                    $movementType = 'out';
                    $movementReason = 'Devolución MERMA (mal estado) - Venta #' . $sale->id;
                }
                
                $lineTotal = round($qtyToReturn * $saleItem->price, 2);
                
                // Crear venta de devolución (negativa)
                $returnSale = Sale::create([
                    'user_id'         => $userId,
                    'cash_shift_id'   => $shift->id,
                    'payment'         => $sale->payment,
                    'subtotal'        => -$lineTotal,
                    'surcharge'       => 0,
                    'fee_pct'         => 0,
                    'total'           => -$lineTotal,
                    'status'          => Sale::STATUS_RETURNED,
                    'original_sale_id'=> $sale->id,
                   'return_reason'   => $data['return_reason'],
                    'cash_received'   => null,
                    'cash_change'     => null,
                ]);
                
                // Crear item de devolución
                SaleItem::create([
                    'sale_id'    => $returnSale->id,
                    'product_id' => $product->id,
                    'qty'        => -$qtyToReturn,
                    'price'      => $saleItem->price,
                    'total'      => -$lineTotal,
                ]);
                
                // ACTUALIZAR STOCK (solo si está en buen estado)
                if ($isGoodCondition) {
                    $product->update(['stock' => $after]);
                }
                
                // REGISTRAR MOVIMIENTO DE INVENTARIO
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
               'message' => '✅ Devolución procesada. El producto regresó al inventario y el dinero fue devuelto',
                'amount_returned' => $qtyToReturn * $saleItem->price,
                
            ], 201);
            
        } catch (\Throwable $e) {
            \Log::error('Return sale item failed', [
                'msg'  => $e->getMessage(),
                'line' => $e->getLine(),
            ]);
            
            return response()->json([
                'message' => 'Error al procesar devolución: ' . $e->getMessage(),
            ], 500);
        }
    }




public function suggestProducts(Request $request)
{
    $q = trim((string)$request->query('q', ''));

    if ($q === '') {
        return response()->json([]);
    }

    // Busca por nombre o por código de barras (parcial)
    $products = \App\Models\Product::query()
        ->when(true, function ($qq) use ($q) {
            $qq->where('name', 'like', "%{$q}%")
               ->orWhere('barcode', 'like', "%{$q}%");
        })
        ->orderBy('name')
        ->limit(15)
        ->get(['id','name','price','category','barcode','photo']);

    // Estructura ligera para el front
    $data = $products->map(function($p){
        return [
            'id'       => $p->id,
            'name'     => $p->name,
            'price'    => (float)$p->price,
            'category' => $p->category,
            'barcode'  => $p->barcode,
            'image'    => method_exists($p, 'getImageUrlAttribute')
                            ? $p->image_url
                            : ($p->photo ?? null),
        ];
    });

    return response()->json($data);
}






    
    
}
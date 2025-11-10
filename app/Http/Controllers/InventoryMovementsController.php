<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\InventoryMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryMovementsController extends Controller
{
    /**
     * Mostrar formulario para crear una entrada o salida.
     */
    public function create()
    {
        // Lista de productos activos, orden alfabético
        $products = Product::where('active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'barcode', 'stock']);

        return view('inventario.movimientos.create', compact('products'));
    }

    /**
     * Registrar un nuevo movimiento (entrada o salida).
     */
  public function store(Request $request)
{
    $data = $request->validate([
        'type'       => 'required|in:in,out',
        'product_id' => 'required|exists:products,id',
        'qty'        => 'required|integer|min:1',
        'reason'     => 'nullable|string|max:255',
        'moved_at'   => 'nullable|date',              // fecha
        'supplier'   => 'nullable|string|max:255',    // proveedor (solo entradas)
        'reference'  => 'nullable|string|max:255',    // factura/nota
        'unit_cost'  => 'nullable|numeric|min:0',     // solo entradas
    ]);

    $product = Product::findOrFail($data['product_id']);

    \DB::transaction(function () use ($data, $product) {
        $before = $product->stock;
        $change = $data['type'] === 'in' ? $data['qty'] : -$data['qty'];
        $after  = max(0, $before + $change);

        // Actualizar stock
        $product->update(['stock' => $after]);

        // Calcular costos (solo entradas)
        $unitCost  = ($data['type'] === 'in' && isset($data['unit_cost'])) ? $data['unit_cost'] : null;
        $totalCost = $unitCost !== null ? round($unitCost * $data['qty'], 2) : null;

        InventoryMovement::create([
            'product_id' => $product->id,
            'type'       => $data['type'],
            'qty'        => $data['qty'],
            'before_qty' => $before,
            'after_qty'  => $after,
            'reason'     => $data['reason'] ?? null,
            'user_id'    => auth()->id(),
            'moved_at'   => $data['moved_at'] ?? now()->toDateString(),
            'supplier'   => $data['type'] === 'in' ? ($data['supplier'] ?? null) : null,
            'reference'  => $data['reference'] ?? null,
            'unit_cost'  => $unitCost,
            'total_cost' => $totalCost,
        ]);
    });

    return redirect()
        ->route('movimientos.create')
        ->with('ok', 'Movimiento registrado correctamente.');
}

}

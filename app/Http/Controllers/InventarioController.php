<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\InventoryMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventarioController extends Controller
{
    // 📦 Listado de productos + buscador
  public function index(Request $request)
{



 $query = DB::table('products');

    // Columnas que podrías o no tener
    $columns      = \Illuminate\Support\Facades\Schema::getColumnListing('products');
    $hasStock     = in_array('stock', $columns);
    $hasMinStock  = in_array('min_stock', $columns);
    $expiryColumn = collect(['expiry_date','expiration_date','expires_at'])
                    ->first(fn($c) => in_array($c, $columns));

    // FILTROS desde el dashboard
    $filter = $request->query('filter'); // low_stock
    $status = $request->query('status'); // expired | soon

    // Bajo stock
    if ($filter === 'low_stock' && $hasStock && $hasMinStock) {
        $query->whereColumn('stock', '<', 'min_stock');
    }

    // Vencidos / por vencer (30 días)
    if ($expiryColumn) {
        $today = now()->toDateString();
        $in30  = now()->addDays(30)->toDateString();

        if ($status === 'expired') {
            $query->whereNotNull($expiryColumn)
                  ->whereDate($expiryColumn, '<', $today);
        } elseif ($status === 'soon') {
            $query->whereNotNull($expiryColumn)
                  ->whereBetween($expiryColumn, [$today, $in30]);
        }
    }



    $q = trim($request->get('q', ''));

    // Base del listado (misma que ya usas para la tabla)
    $base = \App\Models\Product::query()
        ->when($q, function ($query) use ($q) {
            $query->where('name', 'like', "%{$q}%")
                  ->orWhere('barcode', 'like', "%{$q}%")
                  ->orWhere('category', 'like', "%{$q}%");
        })
        ->orderBy('name');











    // ... el resto de tu lógica (joins, selects, orden, etc.)
    $productos = $query->orderBy('name')->paginate(20);
    // Paginado para la tabla
    $products = (clone $base)->paginate(25)->withQueryString();











    // Totales (sobre el conjunto filtrado completo)
    $all = (clone $base)->get();
    $costValue   = $all->sum(fn($p) => (int)$p->stock * (float)($p->cost ?? 0));
    $retailValue = $all->sum(fn($p) => (int)$p->stock * (float)$p->price);

    $totals = [
        'items'            => $all->count(),
        'qty'              => $all->sum('stock'),
        'cost_value'       => $costValue,
        'retail_value'     => $retailValue,
        'potential_margin' => $retailValue - $costValue,
    ];

    // AJAX parcial (cuando escribe en el buscador)
    if ($request->ajax()) {
        return view('inventario.partials.tabla', compact('products'))->render();
    }

    return view('inventario.index', compact('products', 'totals'));
}










    // 🔧 Ajustar stock manualmente
    public function adjust(Request $request, Product $product)
    {
        $data = $request->validate([
            'type'   => 'required|in:in,out,adjust',
            'qty'    => 'required|integer',
            'reason' => 'nullable|string|max:255',
        ]);

        $before = $product->stock;
        $change = match ($data['type']) {
            'in'     => $data['qty'],
            'out'    => -$data['qty'],
            'adjust' => $data['qty'], // puede ser positivo o negativo
        };

        $after = max(0, $before + $change); // nunca negativo

        DB::transaction(function () use ($product, $before, $after, $data, $change) {
            $product->update(['stock' => $after]);

            InventoryMovement::create([
                'product_id' => $product->id,
                'type'       => $data['type'],
                'qty'        => abs($change),
                'before_qty' => $before,
                'after_qty'  => $after,
                'reason'     => $data['reason'] ?? null,
                'user_id'    => auth()->id(),
            ]);
        });

        return back()->with('ok', 'Stock actualizado correctamente.');
    }

    // ⚙️ Actualizar stock mínimo
    public function updateMin(Request $request, Product $product)
    {
        $data = $request->validate(['min_stock' => 'required|integer|min:0']);
        $product->update($data);
        return back()->with('ok', 'Stock mínimo actualizado.');
    }
}

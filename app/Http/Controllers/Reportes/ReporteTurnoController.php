<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Models\SaleItem;
use Illuminate\Http\Request;

class ReporteTurnoController extends Controller
{
    public function categorias(Shift $shift)
    {
        // Ventas por categoría SOLO de este turno
        $rows = SaleItem::query()
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->where('sales.shift_id', $shift->id)
            ->selectRaw('
                categories.id   as category_id,
                categories.name as category_name,
                SUM(sale_items.qty) as unidades_vendidas,
                SUM(sale_items.qty * sale_items.price) as total_vendido
            ')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_vendido')
            ->get();

        $granTotal = $rows->sum('total_vendido');

        return view('reportes.turnos.categorias', [
            'shift'      => $shift,
            'rows'       => $rows,
            'granTotal'  => $granTotal,
        ]);
    }
}

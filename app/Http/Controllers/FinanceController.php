<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use App\Models\Sale;
use App\Models\ClientPayment;
use App\Models\Product;
use App\Models\InventoryTransaction;

class FinanceController extends Controller
{
    public function index(Request $request)
    {
        // 1) Rango de fechas
        $end   = $request->filled('end')
            ? Carbon::parse($request->input('end'))->endOfDay()
            : Carbon::today()->endOfDay();

        $start = $request->filled('start')
            ? Carbon::parse($request->input('start'))->startOfDay()
            : Carbon::today()->subDays(29)->startOfDay();

        // 2) Ventas por método (del RANGO)
        $ventasEfectivo = Sale::whereBetween('created_at', [$start,$end])->where('payment','cash')->sum('total');
        $ventasTarjeta  = Sale::whereBetween('created_at', [$start,$end])->where('payment','card')->sum('total');
        $ventasTransf   = Sale::whereBetween('created_at', [$start,$end])->where('payment','transfer')->sum('total');
        $ventasCredito  = Sale::whereBetween('created_at', [$start,$end])->where('payment','credit')->sum('total');

        // 3) Abonos de crédito (del RANGO)
        $abonosCredito  = ClientPayment::whereBetween('created_at', [$start,$end])->sum('amount');

        // 4) Inventario (del RANGO) — salidas de caja
        $noAnulados = function($q) {
            if (Schema::hasColumn('inventory_transactions','voided')) {
                $q->where(function($w){ $w->whereNull('voided')->orWhere('voided', false); });
            }
        };

        $compras = InventoryTransaction::whereBetween('created_at', [$start,$end])
            ->where('type','in')->where('reason','purchase')
            ->tap($noAnulados)->sum('total_cost');

        $mermas = InventoryTransaction::whereBetween('created_at', [$start,$end])
            ->where('type','out')->whereIn('reason',['waste','damaged','expired'])
            ->tap($noAnulados)->sum('total_cost');

        // 5) Balance (rango)
        $entradasCaja = $ventasEfectivo + $ventasTarjeta + $ventasTransf + $abonosCredito;
        $salidasCaja  = $compras + $mermas;
        $balance      = $entradasCaja - $salidasCaja;

        // 6) Valor actual del inventario (informativo)
        $costCol = Schema::hasColumn('products','cost') ? 'cost'
                : (Schema::hasColumn('products','purchase_cost') ? 'purchase_cost' : 'price'); // último recurso
        $valorInventario = Product::selectRaw("SUM(stock * COALESCE($costCol,0)) as total")->value('total') ?? 0;

        // 7) Ventas por día (tabla)
        $driver   = DB::getDriverName(); // sqlite | mysql | etc.
        $dateExpr = $driver === 'sqlite' ? "DATE(created_at)" : "DATE(created_at)";
        $ventasPorDia = Sale::selectRaw("$dateExpr as fecha, SUM(total) as total")
            ->whereBetween('created_at', [$start,$end])
            ->groupBy('fecha')->orderBy('fecha')->get();

        // 8) Ventas por mes (últimos 12)
        $monthExpr   = $driver === 'sqlite' ? "strftime('%Y-%m', created_at)" : "DATE_FORMAT(created_at, '%Y-%m')";
        $last12Start = Carbon::now()->startOfMonth()->subMonths(11);
        $ventasPorMes = Sale::selectRaw("$monthExpr as ym, SUM(total) as total")
            ->where('created_at','>=',$last12Start)
            ->groupBy('ym')->orderBy('ym')->get();

        return view('finanzas.index', [
            'start' => $start->toDateString(),
            'end'   => $end->toDateString(),

            // Verde (entra a caja)
            'ventasEfectivo' => (float)$ventasEfectivo,
            'ventasTarjeta'  => (float)$ventasTarjeta,
            'ventasTransf'   => (float)$ventasTransf,
            'abonosCredito'  => (float)$abonosCredito,

            // Rojo (no entra o son salidas)
            'ventasCredito'  => (float)$ventasCredito,
            'compras'        => (float)$compras,
            'mermas'         => (float)$mermas,

            'entradasCaja'   => (float)$entradasCaja,
            'salidasCaja'    => (float)$salidasCaja,
            'balance'        => (float)$balance,
            'valorInventario'=> (float)$valorInventario,

            'ventasPorDia'   => $ventasPorDia,
            'ventasPorMes'   => $ventasPorMes,
        ]);
    }
}

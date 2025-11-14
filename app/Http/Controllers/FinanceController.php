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
use App\Models\CashMovement;

class FinanceController extends Controller
{
    /**
     * Panel de finanzas con análisis completo del negocio
     *
     * Calcula: ventas, abonos, otros ingresos, compras, mermas, gastos,
     * balance, ganancias, proyecciones, comparaciones con período anterior,
     * alertas inteligentes y datos para gráficas
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // ========================================
        // 1) RANGO DE FECHAS
        // ========================================
        $end = $request->filled('end')
            ? Carbon::parse($request->input('end'))->endOfDay()
            : Carbon::today()->endOfDay();

        $start = $request->filled('start')
            ? Carbon::parse($request->input('start'))->startOfDay()
            : Carbon::now()->startOfMonth()->startOfDay();

        // Calcular días del período
        $diasPeriodo = $start->diffInDays($end) + 1;

        // ========================================
        // 2) ENTRADAS - VENTAS POR MÉTODO
        // (ventas usan created_at, aquí no hay problema)
        // ========================================
        $ventasEfectivo = Sale::whereBetween('created_at', [$start, $end])
            ->where('payment', 'cash')->sum('total');
        
        $ventasTarjeta = Sale::whereBetween('created_at', [$start, $end])
            ->where('payment', 'card')->sum('total');
        
        $ventasTransf = Sale::whereBetween('created_at', [$start, $end])
            ->where('payment', 'transfer')->sum('total');
        
        $ventasCredito = Sale::whereBetween('created_at', [$start, $end])
            ->where('payment', 'credit')->sum('total');

        // ========================================
        // 3) ENTRADAS - ABONOS POR MÉTODO DE PAGO
        // ========================================
        $abonosEfectivo = ClientPayment::whereBetween('created_at', [$start, $end])
            ->where('method', 'efectivo')->sum('amount');
        
        $abonosTarjeta = ClientPayment::whereBetween('created_at', [$start, $end])
            ->where('method', 'tarjeta')->sum('amount');
        
        $abonosTransferencia = ClientPayment::whereBetween('created_at', [$start, $end])
            ->where('method', 'transferencia')->sum('amount');

        $abonosTotal = $abonosEfectivo + $abonosTarjeta + $abonosTransferencia;

        // ========================================
        // 4) ENTRADAS - OTROS INGRESOS
        // ========================================
        $otrosIngresos = CashMovement::whereBetween('date', [
                $start->toDateString(),
                $end->toDateString(),
            ])
            ->where('type', 'ingreso')
            ->sum('amount');

        $otrosIngresosPorCategoria = CashMovement::whereBetween('date', [
                $start->toDateString(),
                $end->toDateString(),
            ])
            ->where('type', 'ingreso')
            ->selectRaw('COALESCE(custom_category, category) as cat, SUM(amount) as total')
            ->groupBy('cat')
            ->orderBy('total', 'desc')
            ->get();

        // ========================================
        // 5) SALIDAS - INVENTARIO
        //
        // Cambios importantes:
        // - Filtrar ANULADOS con voided_at
        // - Usar moved_at en lugar de created_at
        // - Ajustes (reason = 'adjust') NO entran al balance
        // ========================================

        // 5.1 Filtro reutilizable para excluir transacciones anuladas
        $noAnulados = function ($q) {
            $q->whereNull('voided_at'); // en tu tabla existe voided_at, no "voided"
        };

        // 5.2 COMPRAS de inventario (solo reason = 'purchase')
        $compras = InventoryTransaction::whereBetween('moved_at', [
                $start->toDateString(),
                $end->toDateString(),
            ])
            ->where('type', 'in')
            ->where('reason', 'purchase')   // solo compras reales, sin ajustes
            ->tap($noAnulados)
            ->sum('total_cost');

        // 5.3 MERMAS (solo waste / damaged / expired)
        $mermas = InventoryTransaction::whereBetween('moved_at', [
                $start->toDateString(),
                $end->toDateString(),
            ])
            ->where('type', 'out')
            ->whereIn('reason', ['waste', 'damaged', 'expired']) // sin ajustes
            ->tap($noAnulados)
            ->sum('total_cost');

        // ========================================
        // 6) SALIDAS - GASTOS OPERATIVOS
        // ========================================
        // IMPORTANTE: Excluir 'pago_proveedor' porque las compras ya se cuentan en $compras
        $gastosOperativos = CashMovement::whereBetween('date', [
                $start->toDateString(),
                $end->toDateString(),
            ])
            ->where('type', 'egreso')
            ->where('category', '!=', 'pago_proveedor')
            ->sum('amount');

        $gastosOperativosPorCategoria = CashMovement::whereBetween('date', [
                $start->toDateString(),
                $end->toDateString(),
            ])
            ->where('type', 'egreso')
            ->where('category', '!=', 'pago_proveedor')
            ->selectRaw('COALESCE(custom_category, category) as cat, SUM(amount) as total')
            ->groupBy('cat')
            ->orderBy('total', 'desc')
            ->get();

        // Top 5 gastos más grandes
        $top5Gastos = $gastosOperativosPorCategoria->take(5);

        // ========================================
        // 7) TOTALES Y BALANCE
        // ========================================
        $totalVentas   = $ventasEfectivo + $ventasTarjeta + $ventasTransf;
        $totalEntradas = $totalVentas + $abonosTotal + $otrosIngresos;
        $totalSalidas  = $compras + $mermas + $gastosOperativos;
        $balance       = $totalEntradas - $totalSalidas;

        // ========================================
        // 8) GANANCIAS
        // ========================================
        $gananciaBruta   = $totalVentas - ($compras + $mermas);
        $gananciaNeta    = $gananciaBruta - $gastosOperativos;
        $margenGanancia  = $totalVentas > 0 ? ($gananciaBruta / $totalVentas) * 100 : 0;

        // ========================================
        // 9) INFORMACIÓN ADICIONAL
        // ========================================
        $costCol = Schema::hasColumn('products', 'cost') ? 'cost'
            : (Schema::hasColumn('products', 'purchase_cost') ? 'purchase_cost' : 'price');

        $valorInventario = Product::selectRaw("SUM(stock * COALESCE($costCol,0)) as total")
            ->value('total') ?? 0;

        // Por cobrar: Total acumulado histórico de créditos pendientes
        $totalVentasCredito = Sale::where('payment', 'credit')->sum('total');
        $totalAbonado       = ClientPayment::sum('amount');
        $porCobrar          = max(0, $totalVentasCredito - $totalAbonado);

        $capitalTotal = $balance + $valorInventario + $porCobrar;

        // ========================================
        // 10) PROYECCIÓN DEL MES
        // ========================================
        $hoy      = Carbon::today();
        $finMes   = $hoy->copy()->endOfMonth();
        $diasRestantes    = $hoy->diffInDays($finMes);
        $diasTranscurridos = $hoy->day;

        $promedioEntradas = $diasTranscurridos > 0 ? $totalEntradas / $diasTranscurridos : 0;
        $promedioSalidas  = $diasTranscurridos > 0 ? $totalSalidas / $diasTranscurridos : 0;

        $proyeccionEntradas = $totalEntradas + ($promedioEntradas * $diasRestantes);
        $proyeccionSalidas  = $totalSalidas + ($promedioSalidas * $diasRestantes);
        $proyeccionBalance  = $proyeccionEntradas - $proyeccionSalidas;

        // ========================================
        // 11) COMPARACIÓN CON PERÍODO ANTERIOR
        // ========================================
        $prevStart = $start->copy()->subDays($diasPeriodo);
        $prevEnd   = $end->copy()->subDays($diasPeriodo);

        $prevVentas = Sale::whereBetween('created_at', [$prevStart, $prevEnd])
            ->whereIn('payment', ['cash', 'card', 'transfer'])
            ->sum('total');

        $prevAbonos = ClientPayment::whereBetween('created_at', [$prevStart, $prevEnd])
            ->sum('amount');

        $prevOtrosIngresos = CashMovement::whereBetween('date', [
                $prevStart->toDateString(),
                $prevEnd->toDateString(),
            ])
            ->where('type', 'ingreso')
            ->sum('amount');

        $prevEntradas = $prevVentas + $prevAbonos + $prevOtrosIngresos;

        // Compras y mermas del período anterior (usando moved_at y excluyendo anulados)
        $prevCompras = InventoryTransaction::whereBetween('moved_at', [
                $prevStart->toDateString(),
                $prevEnd->toDateString(),
            ])
            ->where('type', 'in')
            ->where('reason', 'purchase')
            ->tap($noAnulados)
            ->sum('total_cost');

        $prevMermas = InventoryTransaction::whereBetween('moved_at', [
                $prevStart->toDateString(),
                $prevEnd->toDateString(),
            ])
            ->where('type', 'out')
            ->whereIn('reason', ['waste', 'damaged', 'expired'])
            ->tap($noAnulados)
            ->sum('total_cost');

        $prevGastos = CashMovement::whereBetween('date', [
                $prevStart->toDateString(),
                $prevEnd->toDateString(),
            ])
            ->where('type', 'egreso')
            ->where('category', '!=', 'pago_proveedor')
            ->sum('amount');

        $prevSalidas       = $prevCompras + $prevMermas + $prevGastos;
        $prevBalance       = $prevEntradas - $prevSalidas;
        $prevGananciaBruta = $prevVentas - ($prevCompras + $prevMermas);

        // --------- Tarjetas adicionales: Inventario / Por cobrar / Capital ---------

        // Inventario anterior: usamos el valor actual (no llevas histórico de inventario aquí)
        $prevValorInventario = $valorInventario;

        // Por cobrar al inicio del período anterior
        $totalVentasCreditoPrev = Sale::where('payment', 'credit')
            ->where('created_at', '<', $prevStart)
            ->sum('total');

        $totalAbonadoPrev = ClientPayment::where('created_at', '<', $prevStart)
            ->sum('amount');

        $prevPorCobrar = max(0, $totalVentasCreditoPrev - $totalAbonadoPrev);

        // Capital anterior
        $prevCapitalTotal = $prevBalance + $prevValorInventario + $prevPorCobrar;

        // --------- Cambios porcentuales ---------

        // Cambios principales
        $cambioEntradas = $prevEntradas > 0
            ? (($totalEntradas - $prevEntradas) / $prevEntradas) * 100
            : ($totalEntradas > 0 ? 100 : 0);

        $cambioSalidas = $prevSalidas > 0
            ? (($totalSalidas - $prevSalidas) / $prevSalidas) * 100
            : ($totalSalidas > 0 ? 100 : 0);

        $cambioBalance = $prevBalance != 0
            ? (($balance - $prevBalance) / abs($prevBalance)) * 100
            : 0;

        $cambioGanancia = $prevGananciaBruta != 0
            ? (($gananciaBruta - $prevGananciaBruta) / abs($prevGananciaBruta)) * 100
            : ($gananciaBruta > 0 ? 100 : 0);

        $cambioCompras = $prevCompras > 0
            ? (($compras - $prevCompras) / $prevCompras) * 100
            : ($compras > 0 ? 100 : 0);

        // Cambios para las 3 tarjetas adicionales
        $cambioInventario = 0; // sin histórico, asumimos 0
        $cambioPorCobrar  = $prevPorCobrar > 0
            ? (($porCobrar - $prevPorCobrar) / $prevPorCobrar) * 100
            : ($porCobrar > 0 ? 100 : 0);

        $cambioCapital = $prevCapitalTotal > 0
            ? (($capitalTotal - $prevCapitalTotal) / $prevCapitalTotal) * 100
            : ($capitalTotal > 0 ? 100 : 0);

        // ========================================
        // 12) ALERTAS INTELIGENTES
        // ========================================
        $alertas = [];

        if ($cambioSalidas > 20) {
            $alertas[] = [
                'tipo'    => 'warning',
                'icono'   => '⚠️',
                'mensaje' => "Tus gastos aumentaron " . number_format($cambioSalidas, 1) . "% comparado con el período anterior"
            ];
        }

        if ($balance < 0) {
            $alertas[] = [
                'tipo'    => 'danger',
                'icono'   => '🚨',
                'mensaje' => "Balance negativo: Estás gastando más de lo que estás ganando"
            ];
        }

        $cambioVentas = $prevVentas > 0
            ? (($totalVentas - $prevVentas) / $prevVentas) * 100
            : 0;

        if ($cambioVentas < -10) {
            $alertas[] = [
                'tipo'    => 'warning',
                'icono'   => '📉',
                'mensaje' => "Tus ventas bajaron " . number_format(abs($cambioVentas), 1) . "% vs período anterior"
            ];
        }

        if ($balance > 0 && $cambioBalance > 10) {
            $alertas[] = [
                'tipo'    => 'success',
                'icono'   => '🎉',
                'mensaje' => "¡Excelente! Tu balance mejoró " . number_format($cambioBalance, 1) . "% vs período anterior"
            ];
        }

        if ($proyeccionBalance < 0 && $balance > 0) {
            $alertas[] = [
                'tipo'    => 'warning',
                'icono'   => '⚠️',
                'mensaje' => "Si sigues a este ritmo, terminarás el mes con balance negativo"
            ];
        }

        // ========================================
        // 13) DATOS PARA GRÁFICAS
        // ========================================
        $driver   = DB::getDriverName();
        $dateExpr = $driver === 'sqlite'
            ? "DATE(created_at)"
            : "DATE(created_at)";

        // Ventas por día (entradas)
        $ventasPorDia = Sale::selectRaw("$dateExpr as fecha, SUM(total) as total")
            ->whereBetween('created_at', [$start, $end])
            ->whereIn('payment', ['cash', 'card', 'transfer'])
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get()
            ->keyBy('fecha');

        // Gastos operativos por día (salidas, sin pago_proveedor)
        $gastosPorDia = CashMovement::whereBetween('date', [
                $start->toDateString(),
                $end->toDateString(),
            ])
            ->where('type', 'egreso')
            ->where('category', '!=', 'pago_proveedor')
            ->selectRaw('date as fecha, SUM(amount) as total')
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get()
            ->keyBy('fecha');

        // Compras de mercancía por día (usando moved_at y solo purchase)
        $comprasPorDia = InventoryTransaction::selectRaw("DATE(moved_at) as fecha, SUM(total_cost) as total")
            ->whereBetween('moved_at', [
                $start->toDateString(),
                $end->toDateString(),
            ])
            ->where('type', 'in')
            ->where('reason', 'purchase')
            ->tap($noAnulados)
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get()
            ->keyBy('fecha');

        // Combinar para la gráfica (salidas = gastos operativos + compras de mercancía)
        $datosGrafica = [];
        $periodo = Carbon::parse($start);
        while ($periodo <= $end) {
            $fecha      = $periodo->toDateString();
            $gastos     = $gastosPorDia->get($fecha)->total ?? 0;
            $comprasDia = $comprasPorDia->get($fecha)->total ?? 0;

            $datosGrafica[] = [
                'fecha'    => $periodo->format('d/m'),
                'entradas' => $ventasPorDia->get($fecha)->total ?? 0,
                'salidas'  => $gastos + $comprasDia,
            ];
            $periodo->addDay();
        }

        // ========================================
        // RETORNAR VISTA CON TODOS LOS DATOS
        // ========================================
        return view('finanzas.index', compact(
            'start',
            'end',
            'diasPeriodo',
            
            // Ventas
            'ventasEfectivo',
            'ventasTarjeta',
            'ventasTransf',
            'ventasCredito',
            'totalVentas',
            
            // Abonos
            'abonosEfectivo',
            'abonosTarjeta',
            'abonosTransferencia',
            'abonosTotal',
            
            // Otros ingresos
            'otrosIngresos',
            'otrosIngresosPorCategoria',
            
            // Inventario
            'compras',
            'mermas',
            
            // Gastos
            'gastosOperativos',
            'gastosOperativosPorCategoria',
            'top5Gastos',
            
            // Totales
            'totalEntradas',
            'totalSalidas',
            'balance',
            
            // Ganancias
            'gananciaBruta',
            'gananciaNeta',
            'margenGanancia',
            
            // Info adicional
            'valorInventario',
            'porCobrar',
            'capitalTotal',

            // Históricos para tarjetas nuevas
            'prevValorInventario',
            'prevPorCobrar',
            'prevCapitalTotal',
            'cambioInventario',
            'cambioPorCobrar',
            'cambioCapital',
            
            // Proyección
            'proyeccionEntradas',
            'proyeccionSalidas',
            'proyeccionBalance',
            'diasRestantes',
            
            // Comparación
            'prevEntradas',
            'prevSalidas',
            'prevBalance',
            'prevGananciaBruta',
            'prevCompras',
            'cambioEntradas',
            'cambioSalidas',
            'cambioBalance',
            'cambioGanancia',
            'cambioCompras',
            
            // Alertas
            'alertas',
            
            // Gráficas
            'datosGrafica'
        ));
    }
}

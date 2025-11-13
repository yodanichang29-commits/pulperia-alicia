<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // 1) Rango de fechas (today|yesterday|this_month|last_month|custom)
        [$start, $end, $rangeLabel] = $this->resolveRange($request);


    $inicio = \Illuminate\Support\Carbon::parse($start)->startOfDay();
    $fin    = \Illuminate\Support\Carbon::parse($end)->endOfDay();








// --- DÍAS MÁS VENDIDOS (tickets, monto y unidades) ---
$isSqlite = DB::connection()->getDriverName() === 'sqlite';

// Día de la semana 1..7 (Lun..Dom para ambos motores)
$dayExpr = $isSqlite
    ? "CAST(((strftime('%w', s.created_at) + 6) % 7) + 1 AS INTEGER)" // 0..6 => 1..7
    : "((DAYOFWEEK(s.created_at)+5)%7)+1"; // MySQL: 1..7 Dom..Sáb => 1..7 Lun..Dom

// Tickets y monto (tabla sales)
$byDaySales = DB::table('sales as s')
    ->selectRaw("$dayExpr as dow, COUNT(*) as tickets, SUM(s.total) as amount")
    ->whereBetween('s.created_at', [$start, $end])
    ->groupBy('dow')
    ->get();

// Unidades (join a sale_items)
// Unidades por día (Lun..Dom = 1..7 igual que $byDaySales)
$byDayUnits = DB::table('sale_items as si')
    ->join('sales as s','s.id','=','si.sale_id')
    ->selectRaw("$dayExpr as dow, SUM(si.qty) as units")
    ->whereBetween('s.created_at', [$start, $end])
    ->groupBy('dow')
    ->get()
    ->keyBy('dow');

// Mapas de día
$dayNames = [1=>'Lunes',2=>'Martes',3=>'Miércoles',4=>'Jueves',5=>'Viernes',6=>'Sábado',7=>'Domingo'];

$labels = [];
$tickets = [];
$amount  = [];
$units   = [];

for ($d=1; $d<=7; $d++) {
    $labels[]  = $dayNames[$d];
    $rowS      = $byDaySales->firstWhere('dow', $d);
    $rowU      = $byDayUnits->get($d);
    $tickets[] = (int)($rowS->tickets ?? 0);
    $amount[]  = (float)($rowS->amount  ?? 0);
    $units[]   = (int)($rowU->units     ?? 0);
}

$salesByDay = [
    'labels'  => $labels,
    'tickets' => $tickets,
    'amount'  => $amount,
    'units'   => $units,
];


// Normaliza a 1..7 (Lun..Dom) y rellena ceros
$labelsDias = [1=>'Lunes',2=>'Martes',3=>'Miércoles',4=>'Jueves',5=>'Viernes',6=>'Sábado',7=>'Domingo'];
$tickets = $amount = $units = array_fill(1, 7, 0);

foreach ($byDaySales as $r) {
    $d = (int)$r->dow;
    $tickets[$d] = (int)$r->tickets;
    $amount[$d]  = (float)$r->amount;
}
foreach ($byDayUnits as $r) {
    $d = (int)$r->dow;
    $units[$d] = (int)$r->units;
}

// Estructura para la vista
$salesByDay = [
    'labels'  => array_values($labelsDias),
    'tickets' => array_values($tickets),
    'amount'  => array_map(fn($v)=>round($v,2), array_values($amount)),
    'units'   => array_values($units),
];







// =========================
//  A) PARETO / ABC (unidades)
// =========================
$productosRango = DB::table('sale_items as si')
    ->join('sales as s', 's.id', '=', 'si.sale_id')
    ->join('products as p', 'p.id', '=', 'si.product_id')
    ->whereBetween('s.created_at', [$inicio, $fin])
    ->groupBy('si.product_id', 'p.name')
    ->select('si.product_id', 'p.name', DB::raw('SUM(si.qty) as unidades'))
    ->orderByDesc('unidades')
    ->get();

$totalUnidades = max(1, (int)$productosRango->sum('unidades')); // evita división entre 0

$acum = 0;
$abcItems = [];
foreach ($productosRango as $row) {
    $pct = round(($row->unidades * 100) / $totalUnidades, 2);
    $acum += $pct;

    $clase = 'C';
    if ($acum <= 80) {
        $clase = 'A';
    } elseif ($acum <= 95) {
        $clase = 'B';
    }

    $abcItems[] = [
        'name' => $row->name,
        'unidades' => (int)$row->unidades,
        'pct' => $pct,               // % de unidades del total
        'acum' => round($acum, 2),   // % acumulado
        'class' => $clase,
    ];
}

// (opcional) top 12 para la gráfica
$abcChart = array_slice($abcItems, 0, 12);

// =========================
//  B) ESTRELLAS (margen% × rotación)
// =========================
// margen% por producto: ((price - cost) / price) * 100
$stars = DB::table('sale_items as si')
    ->join('sales as s', 's.id', '=', 'si.sale_id')
    ->join('products as p', 'p.id', '=', 'si.product_id')
    ->whereBetween('s.created_at', [$inicio, $fin])
    ->groupBy('si.product_id', 'p.name', 'p.price', 'p.cost')
    ->selectRaw("
        si.product_id,
        p.name,
        SUM(si.qty) as unidades,
        CASE
            WHEN p.price > 0 AND p.cost IS NOT NULL
              THEN ((p.price - p.cost) * 100.0 / p.price)
            ELSE 0
        END as margen_pct
    ")
    ->get()
    ->map(function ($r) {
        $r->unidades = (int)$r->unidades;
        $r->margen_pct = round((float)$r->margen_pct, 2);
        $r->score = round($r->margen_pct * $r->unidades, 2); // ⭐ = margen% × unidades
        return $r;
    })
    ->sortByDesc('score')
    ->values()
    ->all();

// (opcional) top 10 para la gráfica
$starsChart = array_slice($stars, 0, 10);









// === HEATMAP: ventas (tickets) por hora y día (SQLite) ===
// strftime('%w'): 0=Dom .. 6=Sáb  → lo convertimos a 1=Lun .. 7=Dom: (w+6)%7+1
$rawHeat = \Illuminate\Support\Facades\DB::table('sales as s')
    ->selectRaw("
        CAST( ((strftime('%w', s.created_at) + 6) % 7) + 1 AS INTEGER ) as dow,
        CAST( strftime('%H', s.created_at) AS INTEGER ) as hour,
        COUNT(*) AS tickets
    ")
    ->whereBetween('s.created_at', [$inicio, $fin])
    ->groupBy('dow', 'hour')
    ->get();

// Matriz 7x24 rellenada con ceros
$grid = [];
for ($d = 1; $d <= 7; $d++) {
    $grid[$d] = array_fill(0, 24, 0);
}
foreach ($rawHeat as $r) {
    $grid[(int)$r->dow][(int)$r->hour] = (int)$r->tickets;
}

$dayLabels = [
    1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles',
    4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'
];

$seriesHeat = [];
for ($d = 1; $d <= 7; $d++) {
    $row = [];
    for ($h = 0; $h < 24; $h++) {
        // x será la hora (número); la convertimos a a.m./p.m. en JS
        $row[] = ['x' => $h, 'y' => $grid[$d][$h]];
    }
    $seriesHeat[] = ['name' => $dayLabels[$d], 'data' => $row];
}

$heatmap = [
    'hours'  => range(0, 23),
    'series' => $seriesHeat,
];





        // Normaliza a inicio/fin de día (Carbon)
$inicio = \Illuminate\Support\Carbon::parse($start)->startOfDay();
$fin    = \Illuminate\Support\Carbon::parse($end)->endOfDay();


// === KPI: Ventas y Unidades del rango ===
// Periodo actual
$ventasActuales = DB::table('sales')
    ->whereBetween('created_at', [$inicio, $fin])
    ->count();

$unidadesActuales = DB::table('sale_items as si')
    ->join('sales as s', 's.id', '=', 'si.sale_id')
    ->whereBetween('s.created_at', [$inicio, $fin])
    ->sum('si.qty');

// Periodo anterior (misma duración que el rango actual)
$diffDays = $inicio->diffInDays($fin) + 1; // inclusive
$prevFin   = $inicio->copy()->subDay()->endOfDay();
$prevInicio= $prevFin->copy()->subDays($diffDays - 1)->startOfDay();

$ventasPrevias = DB::table('sales')
    ->whereBetween('created_at', [$prevInicio, $prevFin])
    ->count();

$unidadesPrevias = DB::table('sale_items as si')
    ->join('sales as s', 's.id', '=', 'si.sale_id')
    ->whereBetween('s.created_at', [$prevInicio, $prevFin])
    ->sum('si.qty');

// Deltas (%). Si el previo es 0, dejamos null para no dividir entre 0.
$delta = function($actual, $previo) {
    if ((int)$previo === 0) return null;
    return round((($actual - $previo) / $previo) * 100, 1);
};

$kpis = [
    'ventas'          => (int)$ventasActuales,
    'ventas_delta'    => $delta($ventasActuales, $ventasPrevias),
    'unidades'        => (int)$unidadesActuales,
    'unidades_delta'  => $delta($unidadesActuales, $unidadesPrevias),
];





        // 3) Ventas del rango seleccionado (SQLite friendly)
        // NOTA: en SQLite conviene comparar con strings YYYY-MM-DD HH:MM:SS
        $salesRange = DB::table('sales')
            ->whereBetween('created_at', [$start, $end]);

        $ventasTotal = (clone $salesRange)->sum('total');
        $tickets     = (clone $salesRange)->count();

        // Comparaciones rápidas
        [$prevStart, $prevEnd] = $this->previousWindow($start, $end);

        $ventasPrev = DB::table('sales')
            ->whereBetween('created_at', [$prevStart, $prevEnd])
            ->sum('total');

        // 4) Horas pico (histograma 24h)
        $hourly = DB::table('sales')
            ->selectRaw("strftime('%H', created_at) as hour, COUNT(*) as cnt")
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        // 5) Productos que más / menos se mueven (por cantidad)
        $topMovers = DB::table('sale_items as si')
            ->join('sales as s','s.id','=','si.sale_id')
            ->join('products as p','p.id','=','si.product_id')
            ->whereBetween('s.created_at', [$start, $end])
            ->groupBy('si.product_id','p.name')
            ->selectRaw('p.name as product, SUM(si.qty) as qty')
            ->orderByDesc('qty')
            ->limit(10)
            ->get();

        // Menos movimiento: incluimos los que no vendieron (LEFT JOIN)
        $slowMovers = DB::table('products as p')
            ->leftJoin('sale_items as si','si.product_id','=','p.id')
            ->leftJoin('sales as s','s.id','=','si.sale_id')
            ->where(function($q) use ($start,$end){
                $q->whereNull('s.created_at')
                  ->orWhereBetween('s.created_at', [$start,$end]);
            })
            ->groupBy('p.id','p.name')
            ->selectRaw("p.name as product, COALESCE(SUM(si.qty),0) as qty")
            ->orderBy('qty','asc')
            ->limit(10)
            ->get();


// 6) Mayor margen (por unidad, independiente de ventas)
// Solo productos con costo > 0 y precio > 0 (evita 100% "falso")
$marginProducts = DB::table('products')
    ->where('active', 1)
    ->where('cost', '>', 0)
    ->where('price', '>', 0)
    ->select([
        'name',
        'price',
        'cost',
        // Abs: ganancia por unidad (float forzado)
        DB::raw('((price - cost) * 1.0) AS margin_abs'),
        // %: MUY IMPORTANTE en SQLite: multiplicar por 100.0 para forzar float
        DB::raw('ROUND(((price - cost) * 100.0) / price, 2) AS margin_pct'),
    ])
    ->orderByDesc('margin_pct')   // cambia a margin_abs si quieres ordenar por L/ud
    ->limit(8)
    ->get()
    ->map(function ($r) {
        return (object)[
            'name'   => $r->name,
            'margin' => (float) $r->margin_pct,  // <-- el JS usa "margin"
            'abs'    => (float) $r->margin_abs,  // opcional
        ];
    });


        // 7) Ventas por mes (últimos 12)
        $salesByMonth = DB::table('sales')
            ->selectRaw("strftime('%Y-%m', created_at) as ym, SUM(total) as total")
            ->where('created_at','>=', Carbon::today()->subMonths(12)->startOfMonth()->toDateTimeString())
            ->groupBy('ym')
            ->orderBy('ym')
            ->get();

            $totalAll = max(1, $salesByMonth->sum('total'));
$salesByMonth = $salesByMonth->map(function($s) use ($totalAll){
    $s->pct = round(($s->total / $totalAll) * 100, 1);
    return $s;
});


  // 8) Métodos de pago (porcentaje en el rango) — normalizados
$pmCounts = DB::table('sales')
    ->selectRaw("
        CASE
          WHEN lower(trim(payment)) IN ('cash','efectivo') THEN 'efectivo'
          WHEN lower(trim(payment)) IN ('card','tarjeta') THEN 'tarjeta'
          WHEN lower(trim(payment)) IN ('transfer','transferencia','bank') THEN 'transferencia'
          WHEN lower(trim(payment)) IN ('credit','crédito','credito','credito') THEN 'credito'
        
        END AS method,
        COUNT(*) AS cnt
    ")
    ->whereBetween('created_at', [$start, $end])
    ->groupBy('method')
    ->get();

$pmTotal = (int) $pmCounts->sum('cnt');

// Orden/llaves “oficiales” para el frontend
$known = collect(['efectivo','tarjeta','transferencia','credito']);

if ($pmTotal === 0) {
    $paymentShare = $known->map(fn($m) => ['method'=>$m,'pct'=>0])->values();
} else {
    $paymentShare = $known->map(function ($m) use ($pmCounts, $pmTotal) {
        $row = $pmCounts->firstWhere('method', $m);
        $pct = $row ? round(($row->cnt / $pmTotal) * 100, 1) : 0;
        return ['method' => $m, 'pct' => $pct];
    })->values();
}

















// --- Alertas de productos (una sola fuente de verdad) ---
$today = \Carbon\Carbon::today()->toDateString();
$in30  = \Carbon\Carbon::today()->addDays(30)->toDateString();

// Bajo stock (stock <= min_stock, para incluir cuando son iguales)
$lowStock = DB::table('products')
    ->where('active', 1)
    ->whereNotNull('min_stock')
    ->where('min_stock', '>', 0)
    ->whereColumn('stock', '<=', 'min_stock')
    ->select('id','name','stock','min_stock','expires_at')
    ->orderByRaw('(min_stock - stock) DESC')
    ->get();

// Vencidos (fecha < hoy)
$expired = DB::table('products')
    ->whereNotNull('expires_at')
    ->whereDate('expires_at','<',$today)
    ->select('id','name','stock','expires_at')
    ->orderBy('expires_at')
    ->get();

// Por vencer (hoy..+30)
$expiring = DB::table('products')
    ->whereNotNull('expires_at')
    ->whereDate('expires_at','>=',$today)
    ->whereDate('expires_at','<=',$in30)
    ->select('id','name','stock','expires_at')
    ->orderBy('expires_at')
    ->get();

// Contadores para las tarjetas (del MISMO bloque)
$lowStockCount = $lowStock->count();
$expiredCount  = $expired->count();
$expiringCount = $expiring->count();









// 4) Estructura de comparación que usará la vista
$compare = [
    'current'  => $ventasTotal,
    'previous' => $ventasPrev,
];



// === Horas pico (histograma 24h) ===
$hourly = DB::table('sales')
    ->selectRaw("strftime('%H', created_at) as hour, COUNT(*) as cnt")
    ->whereBetween('created_at', [$start, $end])
    ->groupBy('hour')
    ->orderBy('hour')
    ->get();






return view('dashboard.index', [
    // ——— Rango y comparación ———
    'range'   => ['start' => $start, 'end' => $end, 'label' => $rangeLabel],
    'compare' => ['current' => $ventasTotal, 'previous' => $ventasPrev],

    // ——— Alertas (listas) ———
    'lowStock' => $lowStock,
    'expired'  => $expired,
    'expiring' => $expiring,

    // ——— Alertas (contadores para tarjetas) ———
    'lowStockCount' => $lowStockCount,
    'expiredCount'  => $expiredCount,
    'expiringCount' => $expiringCount,

    // ——— Ritmo ———
    'hourly'     => $hourly,
    'topMovers'  => $topMovers,
    'slowMovers' => $slowMovers,

    // ——— Rentabilidad y ventas ———
    'marginProducts' => $marginProducts,
    'salesByMonth'   => $salesByMonth,
    'paymentShare'   => $paymentShare,
    'salesByDay'  => $salesByDay,

    // ——— KPIs & Heatmap ———
    'kpis'    => $kpis,
    'heatmap' => $heatmap,

    // ——— ABC y Estrellas (nuevos) ———
    'abcChart'   => $abcChart,
    'starsChart' => $starsChart,
]);
} 

    private function resolveRange(Request $request)
    {
        $mode  = $request->query('range','this_month');
        $today = Carbon::today();
        switch ($mode) {
            case 'today':
                $start = $today->copy()->startOfDay();
                $end   = $today->copy()->endOfDay();
                $label = 'Hoy';
                break;
            case 'yesterday':
                $start = $today->copy()->subDay()->startOfDay();
                $end   = $today->copy()->subDay()->endOfDay();
                $label = 'Ayer';
                break;
            case 'last_month':
                $start = $today->copy()->subMonth()->startOfMonth();
                $end   = $today->copy()->subMonth()->endOfMonth();
                $label = 'Mes pasado';
                break;
            case 'custom':
                $start = Carbon::parse($request->query('start'))->startOfDay();
                $end   = Carbon::parse($request->query('end'))->endOfDay();
                $label = 'Personalizado';
                break;
            case 'this_month':
            default:
                $start = $today->copy()->startOfMonth();
                $end   = $today->copy()->endOfMonth();
                $label = 'Este mes';
        }
        return [$start->toDateTimeString(), $end->toDateTimeString(), $label];
    }

    private function previousWindow($start, $end)
    {
        $s = Carbon::parse($start);
        $e = Carbon::parse($end);
        $diffDays = $s->diffInDays($e) + 1;
        $prevEnd   = $s->copy()->subDay()->endOfDay();
        $prevStart = $prevEnd->copy()->subDays($diffDays-1)->startOfDay();
        return [$prevStart->toDateTimeString(), $prevEnd->toDateTimeString()];
    }
}

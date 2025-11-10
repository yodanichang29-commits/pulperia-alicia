<?php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Reportes\ReporteVentasExport;
use App\Models\Product;
use App\Models\SaleItem; 



class ReporteVentasController extends Controller
{

public function show($id)
{
    // Detectar el motor de base de datos (SQLite o MySQL)
    $driver = \DB::getDriverName();

    // Según el motor, usamos las funciones adecuadas para fecha y hora
    $fechaSql = $driver === 'sqlite'
        ? "DATE(s.created_at)"
        : "DATE_FORMAT(s.created_at, '%Y-%m-%d')";
    $horaSql = $driver === 'sqlite'
        ? "strftime('%H:%M:%S', s.created_at)"
        : "DATE_FORMAT(s.created_at, '%H:%i:%s')";

    // Obtener la venta
    $venta = \DB::table('sales as s')
        ->leftJoin('users as u', 'u.id', '=', 's.user_id')
        ->leftJoin('clients as c', 'c.id', '=', 's.client_id')
        ->where('s.id', $id)
        ->selectRaw("
            s.*,
            {$fechaSql} as fecha,
            {$horaSql} as hora,
            COALESCE(u.name,'-') as cajero,
            COALESCE(c.name,'-') as cliente
        ")
        ->first();

    // Si no existe la venta, devolver error 404
    if (!$venta) {
        abort(404, 'Venta no encontrada');
    }

    // Obtener los productos vendidos en esa venta
    $items = \DB::table('sale_items as si')
        ->join('products as p', 'p.id', '=', 'si.product_id')
        ->where('si.sale_id', $id)
        ->select([
            'p.name as producto',
            'si.qty',
            'si.price',
            'si.total',
        ])
        ->orderBy('si.id')
        ->get();

    // Enviar los datos a la vista
    return view('reportes.ventas.venta_show', compact('venta', 'items'));
}





public function detalle(Request $r)
{
    // Filtros
    $start   = $r->input('start', now()->toDateString());
    $end     = $r->input('end',   now()->toDateString());
    $userId  = $r->input('user_id');            // opcional
    $payment = $r->input('payment');            // cash|card|transfer|credit|null = todos
    $qSaleId = trim($r->input('sale_id', ''));  // opcional, buscar venta específica

    $driver = DB::getDriverName();
    // Compatibilidad fecha/hora
    $DATE = $driver === 'sqlite' ? "DATE(s.created_at)"                 : "DATE(s.created_at)";
    $TIME = $driver === 'sqlite' ? "strftime('%H:%M:%S', s.created_at)" : "DATE_FORMAT(s.created_at,'%H:%i:%s')";

    // Base ventas
    $base = DB::table('sales as s')
        ->leftJoin('users as u', 'u.id', '=', 's.user_id')
        ->leftJoin('clients as c', 'c.id', '=', 's.client_id')
        ->whereBetween('s.created_at', [
            \Illuminate\Support\Carbon::parse($start)->startOfDay(),
            \Illuminate\Support\Carbon::parse($end)->endOfDay(),
        ]);

    if ($userId)  $base->where('s.user_id', $userId);
    if ($payment) $base->where('s.payment', $payment);
    if ($qSaleId !== '') $base->where('s.id', $qSaleId);

    // Traer ventas (cabecera por ticket)
    $ventas = (clone $base)
        ->selectRaw("
            s.id,
            {$DATE} as fecha,
            {$TIME} as hora,
            s.created_at,
            COALESCE(u.name,'-')  as cajero,
            s.payment,
            s.subtotal,
            s.surcharge,
            s.fee_pct,
            s.total,
            s.cash_received,
            s.cash_change,
            COALESCE(c.name,'-')  as cliente
        ")
        ->orderBy('s.created_at', 'asc')
        ->get();

    // Si no hay ventas, devolvemos vista “vacía” con filtros
    if ($ventas->isEmpty()) {
        return view('reportes.ventas.detalle', [
            'ventas' => collect(),
            'itemsPorVenta' => collect(),
            'usuarios' => DB::table('users')->select('id','name')->orderBy('name')->get(),
            'filtros' => compact('start','end','userId','payment','qSaleId'),
            'totales' => ['ventas'=>0,'subtotal'=>0,'surcharge'=>0,'fees'=>0,'total'=>0,'cash'=>0,'change'=>0],
        ]);
    }

    // Traer items de esas ventas (detalle)
    $saleIds = $ventas->pluck('id')->all();

    $items = DB::table('sale_items as si')
        ->join('products as p','p.id','=','si.product_id')
        ->whereIn('si.sale_id', $saleIds)
        ->selectRaw("
            si.sale_id,
            p.name       as producto,
            si.qty       as qty,
            si.price     as price,
            si.total     as total
        ")
        ->orderBy('si.sale_id')
        ->orderBy('p.name')
        ->get()
        ->groupBy('sale_id');

    // Totales de la grilla
    $totales = [
        'ventas'    => $ventas->count(),
        'subtotal'  => $ventas->sum('subtotal'),
        'surcharge' => $ventas->sum('surcharge'),
        'fees'      => $ventas->sum(function($v){ return ($v->fee_pct ?? 0) * 0; /* si usas fee en L, cámbialo */ }),
        'total'     => $ventas->sum('total'),
        'cash'      => $ventas->sum('cash_received'),
        'change'    => $ventas->sum('cash_change'),
    ];

    return view('reportes.ventas.detalle', [
        'ventas'        => $ventas,
        'itemsPorVenta' => $items,
        'usuarios'      => DB::table('users')->select('id','name')->orderBy('name')->get(),
        'filtros'       => compact('start','end','userId','payment','qSaleId'),
        'totales'       => $totales,
    ]);
}






// === VENTAS POR PRODUCTO (vista + filtro) ===

public function porProducto(Request $r)
{
    // === Filtros ===
    $start     = $r->input('start', Carbon::now()->startOfMonth()->toDateString());
    $end       = $r->input('end', Carbon::now()->toDateString());
    $productId = $r->input('product_id');
    $group     = $r->input('group', 'venta'); // venta | dia | precio | hora

    $producto  = null;

    // === Variables por defecto (para evitar errores en la vista) ===
    $resumen  = (object)['qty'=>0,'total'=>0,'precio_prom'=>0];
    $porVenta = collect();
    $porDia   = collect();
    $porPrecio= collect();
    $porHora  = collect();

    // === Compatibilidad: funciones de fecha por motor ===
    $driver = DB::connection()->getDriverName();
    $DATE      = $driver === 'sqlite' ? "DATE(s.created_at)"                       : "DATE(s.created_at)";
    $TIME      = $driver === 'sqlite' ? "strftime('%H:%M', s.created_at)"          : "DATE_FORMAT(s.created_at, '%H:%i')";
    $HOURBLOCK = $driver === 'sqlite' ? "strftime('%Y-%m-%d %H:00', s.created_at)" : "DATE_FORMAT(s.created_at, '%Y-%m-%d %H:00')";

    if ($productId) {
        $producto = Product::find($productId);

        $startDt = Carbon::parse($start)->startOfDay()->toDateTimeString();
        $endDt   = Carbon::parse($end)->endOfDay()->toDateTimeString();

        // === Base Query ===
        $base = DB::table('sale_items as si')
            ->join('sales as s', 's.id', '=', 'si.sale_id')
            ->where('si.product_id', $productId)
            ->whereBetween('s.created_at', [$startDt, $endDt]);

        // === Resumen general ===
        $resumen = (clone $base)
            ->selectRaw("
                SUM(si.qty)   as qty,
                SUM(si.total) as total,
                CASE WHEN SUM(si.qty)=0 THEN 0
                     ELSE ROUND(SUM(si.total)/SUM(si.qty),2)
                END as precio_prom
            ")
            ->first();

        // === Por venta ===
        $porVenta = (clone $base)
            ->selectRaw("
                s.id            as sale_id,
                {$DATE}         as fecha,
                {$TIME}         as hora,
                si.qty          as qty,
                si.price        as price,
                si.total        as total
            ")
            ->orderBy('s.created_at')
            ->get();

        // === Por día ===
        $porDia = (clone $base)
            ->selectRaw("
                {$DATE}         as fecha,
                SUM(si.qty)     as qty,
                SUM(si.total)   as total,
                CASE WHEN SUM(si.qty)=0 THEN 0
                     ELSE ROUND(SUM(si.total)/SUM(si.qty),2)
                END as precio_prom
            ")
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        // === Por precio ===
        $porPrecio = (clone $base)
            ->selectRaw("
                si.price        as price,
                SUM(si.qty)     as qty,
                SUM(si.total)   as total,
                CASE WHEN SUM(si.qty)=0 THEN 0
                     ELSE ROUND(SUM(si.total)/SUM(si.qty),2)
                END as precio_prom
            ")
            ->groupBy('si.price')
            ->orderBy('si.price')
            ->get();

        // === Por hora ===
        $porHora = (clone $base)
            ->selectRaw("
                {$HOURBLOCK}    as hora_bloque,
                SUM(si.qty)     as qty,
                SUM(si.total)   as total,
                CASE WHEN SUM(si.qty)=0 THEN 0
                     ELSE ROUND(SUM(si.total)/SUM(si.qty),2)
                END as precio_prom
            ")
            ->groupBy('hora_bloque')
            ->orderBy('hora_bloque')
            ->get();
    }

    // === Retornar vista ===
    return view('reportes.ventas.por_producto', [
        'start'      => $start,
        'end'        => $end,
        'productId'  => $productId,
        'producto'   => $producto,
        'group'      => $group,

        'resumen'    => $resumen,
        'porVenta'   => $porVenta,
        'porDia'     => $porDia,
        'porPrecio'  => $porPrecio,
        'porHora'    => $porHora,
    ]);
}

// === Autocomplete: nombre o código de barras ===
public function buscarProductos(Request $r)
{
    $q = trim($r->get('q',''));
    if ($q === '') return response()->json([]);

    $items = Product::query()
        ->where(function($w) use ($q){
            $w->where('name','like',"%{$q}%")
              ->orWhere('barcode','like',"%{$q}%");
        })
        ->orderBy('name')
        ->limit(10)
        ->get(['id','name','barcode','price']);

    return response()->json(
        $items->map(fn($p)=>[
            'id'    => $p->id,
            'label' => ($p->barcode ? "{$p->barcode} — " : '').$p->name,
            'price' => $p->price,
        ])
    );
}


    


public function porProveedor(\Illuminate\Http\Request $request)
{
    $desde = $request->input('desde') ?: now()->startOfMonth()->format('Y-m-d');
    $hasta = $request->input('hasta') ?: now()->endOfMonth()->format('Y-m-d');
    $q     = trim($request->input('q', ''));

    // --- Resumen por proveedor ---
    $rows = \DB::table('sale_items as si')
        ->join('sales as s', 's.id', '=', 'si.sale_id')
        ->join('products as p', 'p.id', '=', 'si.product_id')
        ->leftJoin('providers as pv', 'pv.id', '=', 'p.provider_id')
        ->when($desde, fn($qq) => $qq->whereDate('s.created_at', '>=', $desde))
        ->when($hasta, fn($qq) => $qq->whereDate('s.created_at', '<=', $hasta))
        ->when($q, function ($qq) use ($q) {
            $qq->where(function ($w) use ($q) {
                $w->where('pv.name', 'like', "%{$q}%")
                  ->orWhere('p.name', 'like', "%{$q}%");
            });
        })
        ->selectRaw('COALESCE(pv.name,"(Sin proveedor)") as proveedor')
        ->selectRaw('SUM(si.qty) as unidades')                // <-- cambia a si.cantidad si aplica
        ->selectRaw('SUM(si.qty * si.price) as total_vendido')// <-- idem
        ->groupBy('proveedor')
        ->orderByDesc('total_vendido')
        ->get();

    $total_general = $rows->sum('total_vendido');

    // --- Detalle de productos (según filtros actuales) ---
    $detalle = \DB::table('sale_items as si')
        ->join('sales as s', 's.id', '=', 'si.sale_id')
        ->join('products as p', 'p.id', '=', 'si.product_id')
        ->leftJoin('providers as pv', 'pv.id', '=', 'p.provider_id')
        ->when($desde, fn($qq) => $qq->whereDate('s.created_at', '>=', $desde))
        ->when($hasta, fn($qq) => $qq->whereDate('s.created_at', '<=', $hasta))
        ->when($q, function ($qq) use ($q) {
            $qq->where(function ($w) use ($q) {
                $w->where('p.name', 'like', "%{$q}%")
                  ->orWhere('pv.name', 'like', "%{$q}%");
            });
        })
        ->selectRaw('p.name as producto')
        ->selectRaw('COALESCE(pv.name,"(Sin proveedor)") as proveedor')
        ->selectRaw('SUM(si.qty) as unidades')                 // <-- cambia a si.cantidad si aplica
        ->selectRaw('SUM(si.qty * si.price) as total_vendido') // <-- idem
        ->groupBy('producto','proveedor')
        ->orderByDesc('total_vendido')
        ->limit(50) // para que sea ágil; luego ponemos paginación si quieres
        ->get();

    if ($request->ajax()) {
        return view('reportes.ventas.partials.proveedores_contenido', compact('rows','total_general','detalle'))->render();
    }

    return view('reportes.ventas.proveedores', compact('rows','total_general','desde','hasta','q','detalle'));
}





    /**
     * Página principal con filtros + 3 resúmenes.
     * URL: /reportes/ventas
     */
    public function index(Request $request)
    {
        // --- 1) Leer filtros de la URL ---
        // Formato esperado: YYYY-MM-DD (inputs type="date")
        $start = $request->input('start'); // fecha inicial (incluida)
        $end   = $request->input('end');   // fecha final (incluida)
        $userId   = $request->input('user_id');  // opcional
        $payment  = $request->input('payment');  // opcional: cash|card|transfer|credit

        // Si no envían fechas, por defecto hoy
        if (!$start || !$end) {
            $hoy = Carbon::today();
            $start = $start ?: $hoy->format('Y-m-d');
            $end   = $end   ?: $hoy->format('Y-m-d');
        }

        // Rangos completos del día (00:00:00 a 23:59:59)
        $startDt = Carbon::parse($start)->startOfDay();
        $endDt   = Carbon::parse($end)->endOfDay();

        // --- 2) Base query de ventas con filtros comunes ---
        $base = DB::table('sales as s')
            ->leftJoin('users as u', 'u.id', '=', 's.user_id')
            ->whereBetween('s.created_at', [$startDt, $endDt]);

        if ($userId) {
            $base->where('s.user_id', $userId);
        }
        if ($payment) {
            $base->where('s.payment', $payment);
        }

        // --- 3) Resumen por DÍA ---
        // Usamos DATE(s.created_at) para agrupar en SQLite.
        $porDia = (clone $base)
            ->selectRaw("
                DATE(s.created_at) as fecha,
                COUNT(*) as ventas,
                SUM(s.total) as total,
                SUM(CASE WHEN s.payment = 'cash' THEN s.total ELSE 0 END) as efectivo,
                SUM(CASE WHEN s.payment = 'card' THEN s.total ELSE 0 END) as tarjeta,
                SUM(CASE WHEN s.payment = 'transfer' THEN s.total ELSE 0 END) as transferencia,
                SUM(CASE WHEN s.payment = 'credit' THEN s.total ELSE 0 END) as credito
            ")
            ->groupByRaw("DATE(s.created_at)")
            ->orderBy('fecha', 'asc')
            ->get();

        // --- 4) Resumen por USUARIO ---
        $porUsuario = (clone $base)
            ->selectRaw("
                COALESCE(u.name, 'Desconocido') as usuario,
                s.user_id as user_id,
                COUNT(*) as ventas,
                SUM(s.total) as total,
                SUM(CASE WHEN s.payment = 'cash' THEN s.total ELSE 0 END) as efectivo,
                SUM(CASE WHEN s.payment = 'card' THEN s.total ELSE 0 END) as tarjeta,
                SUM(CASE WHEN s.payment = 'transfer' THEN s.total ELSE 0 END) as transferencia,
                SUM(CASE WHEN s.payment = 'credit' THEN s.total ELSE 0 END) as credito
            ")
            ->groupBy('s.user_id', 'u.name')
            ->orderBy('usuario', 'asc')
            ->get();

        // --- 5) Resumen por MÉTODO ---
        $porMetodo = (clone $base)
            ->selectRaw("
                s.payment as metodo,
                COUNT(*) as ventas,
                SUM(s.total) as total
            ")
            ->groupBy('s.payment')
            ->orderBy('metodo', 'asc')
            ->get();


    // --- Totales para pie de tablas ---
$totalesDia = [
    'ventas'        => $porDia->sum('ventas'),
    'total'         => $porDia->sum('total'),
    'efectivo'      => $porDia->sum('efectivo'),
    'tarjeta'       => $porDia->sum('tarjeta'),
    'transferencia' => $porDia->sum('transferencia'),
    'credito'       => $porDia->sum('credito'),
];

$totalesUsuario = [
    'ventas'        => $porUsuario->sum('ventas'),
    'total'         => $porUsuario->sum('total'),
    'efectivo'      => $porUsuario->sum('efectivo'),
    'tarjeta'       => $porUsuario->sum('tarjeta'),
    'transferencia' => $porUsuario->sum('transferencia'),
    'credito'       => $porUsuario->sum('credito'),
];

$totalesMetodo = [
    'ventas' => $porMetodo->sum('ventas'),
    'total'  => $porMetodo->sum('total'),
];


        // Para los selects del filtro
        $usuarios = DB::table('users')->select('id', 'name')->orderBy('name')->get();

        $metodos = [
            ['key' => 'cash',     'label' => 'Efectivo'],
            ['key' => 'card',     'label' => 'Tarjeta'],
            ['key' => 'transfer', 'label' => 'Transferencia'],
            ['key' => 'credit',   'label' => 'Crédito'],
        ];

        return view('reportes.ventas.index', [
            'filtros' => [
                'start'   => $start,
                'end'     => $end,
                'user_id' => $userId,
                'payment' => $payment,
            ],
            'usuarios'   => $usuarios,
            'metodos'    => $metodos,
            'porDia'     => $porDia,
            'porUsuario' => $porUsuario,
            'porMetodo'  => $porMetodo,

'totalesDia'     => $totalesDia,
    'totalesUsuario' => $totalesUsuario,
    'totalesMetodo'  => $totalesMetodo,


        ]);
    }

    /**
     * Exporta a CSV lo que se ve (los tres resúmenes con los mismos filtros).
     * URL: /reportes/ventas/export
     */
    public function exportCsv(Request $request)
    {
        // Reutilizamos la misma lógica que index(), pero devolviendo CSV.
        // Para evitar duplicar demasiada lógica, repetimos los filtros y queries rápido:

        $start = $request->input('start');
        $end   = $request->input('end');
        $userId   = $request->input('user_id');
        $payment  = $request->input('payment');

        if (!$start || !$end) {
            $hoy = Carbon::today();
            $start = $start ?: $hoy->format('Y-m-d');
            $end   = $end   ?: $hoy->format('Y-m-d');
        }

        $startDt = Carbon::parse($start)->startOfDay();
        $endDt   = Carbon::parse($end)->endOfDay();

        $base = DB::table('sales as s')
            ->leftJoin('users as u', 'u.id', '=', 's.user_id')
            ->whereBetween('s.created_at', [$startDt, $endDt]);

        if ($userId) $base->where('s.user_id', $userId);
        if ($payment) $base->where('s.payment', $payment);

        $porDia = (clone $base)
            ->selectRaw("
                DATE(s.created_at) as fecha,
                COUNT(*) as ventas,
                SUM(s.total) as total,
                SUM(CASE WHEN s.payment = 'cash' THEN s.total ELSE 0 END) as efectivo,
                SUM(CASE WHEN s.payment = 'card' THEN s.total ELSE 0 END) as tarjeta,
                SUM(CASE WHEN s.payment = 'transfer' THEN s.total ELSE 0 END) as transferencia,
                SUM(CASE WHEN s.payment = 'credit' THEN s.total ELSE 0 END) as credito
            ")
            ->groupByRaw("DATE(s.created_at)")
            ->orderBy('fecha', 'asc')
            ->get();

        $porUsuario = (clone $base)
            ->selectRaw("
                COALESCE(u.name, 'Desconocido') as usuario,
                s.user_id as user_id,
                COUNT(*) as ventas,
                SUM(s.total) as total,
                SUM(CASE WHEN s.payment = 'cash' THEN s.total ELSE 0 END) as efectivo,
                SUM(CASE WHEN s.payment = 'card' THEN s.total ELSE 0 END) as tarjeta,
                SUM(CASE WHEN s.payment = 'transfer' THEN s.total ELSE 0 END) as transferencia,
                SUM(CASE WHEN s.payment = 'credit' THEN s.total ELSE 0 END) as credito
            ")
            ->groupBy('s.user_id', 'u.name')
            ->orderBy('usuario', 'asc')
            ->get();

        $porMetodo = (clone $base)
            ->selectRaw("
                s.payment as metodo,
                COUNT(*) as ventas,
                SUM(s.total) as total
            ")
            ->groupBy('s.payment')
            ->orderBy('metodo', 'asc')
            ->get();


            $totalesDia = [
    'ventas'        => $porDia->sum('ventas'),
    'total'         => $porDia->sum('total'),
    'efectivo'      => $porDia->sum('efectivo'),
    'tarjeta'       => $porDia->sum('tarjeta'),
    'transferencia' => $porDia->sum('transferencia'),
    'credito'       => $porDia->sum('credito'),
];
$totalesUsuario = [
    'ventas'        => $porUsuario->sum('ventas'),
    'total'         => $porUsuario->sum('total'),
    'efectivo'      => $porUsuario->sum('efectivo'),
    'tarjeta'       => $porUsuario->sum('tarjeta'),
    'transferencia' => $porUsuario->sum('transferencia'),
    'credito'       => $porUsuario->sum('credito'),
];
$totalesMetodo = [
    'ventas' => $porMetodo->sum('ventas'),
    'total'  => $porMetodo->sum('total'),
];


        // --- Construir CSV en memoria ---
        $filename = "reporte_ventas_{$start}_{$end}.csv";
        $headers  = [
            'Content-type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];
$callback = function() use ($porDia, $porUsuario, $porMetodo, $start, $end, $totalesDia, $totalesUsuario, $totalesMetodo) {
    $out = fopen('php://output', 'w');

    // BOM para Excel en Windows
    fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

    // Encabezado
    fputcsv($out, ["REPORTE GENERAL DE VENTAS"]);
    fputcsv($out, ["Rango", "{$start} a {$end}"]);

    // ========== 1) POR DÍA ==========
    fputcsv($out, []); // línea en blanco
    fputcsv($out, ["RESUMEN POR DÍA"]);
    fputcsv($out, ["Fecha", "# Ventas", "Total", "Efectivo", "Tarjeta", "Transferencia", "Crédito"]);

    foreach ($porDia as $r) {
        fputcsv($out, [
            $r->fecha,
            $r->ventas,
            number_format($r->total ?? 0, 2, '.', ''),
            number_format($r->efectivo ?? 0, 2, '.', ''),
            number_format($r->tarjeta ?? 0, 2, '.', ''),
            number_format($r->transferencia ?? 0, 2, '.', ''),
            number_format($r->credito ?? 0, 2, '.', ''),
        ]);
    }
    // Totales por día (una sola fila)
    fputcsv($out, [
        'TOTAL',
        $totalesDia['ventas'],
        number_format($totalesDia['total'] ?? 0, 2, '.', ''),
        number_format($totalesDia['efectivo'] ?? 0, 2, '.', ''),
        number_format($totalesDia['tarjeta'] ?? 0, 2, '.', ''),
        number_format($totalesDia['transferencia'] ?? 0, 2, '.', ''),
        number_format($totalesDia['credito'] ?? 0, 2, '.', ''),
    ]);

    // ========== 2) POR USUARIO ==========
    fputcsv($out, []);
    fputcsv($out, ["RESUMEN POR USUARIO"]);
    fputcsv($out, ["Usuario", "# Ventas", "Total", "Efectivo", "Tarjeta", "Transferencia", "Crédito"]);

    foreach ($porUsuario as $r) {
        fputcsv($out, [
            $r->usuario,
            $r->ventas,
            number_format($r->total ?? 0, 2, '.', ''),
            number_format($r->efectivo ?? 0, 2, '.', ''),
            number_format($r->tarjeta ?? 0, 2, '.', ''),
            number_format($r->transferencia ?? 0, 2, '.', ''),
            number_format($r->credito ?? 0, 2, '.', ''),
        ]);
    }
    // Totales por usuario
    fputcsv($out, [
        'TOTAL',
        $totalesUsuario['ventas'],
        number_format($totalesUsuario['total'] ?? 0, 2, '.', ''),
        number_format($totalesUsuario['efectivo'] ?? 0, 2, '.', ''),
        number_format($totalesUsuario['tarjeta'] ?? 0, 2, '.', ''),
        number_format($totalesUsuario['transferencia'] ?? 0, 2, '.', ''),
        number_format($totalesUsuario['credito'] ?? 0, 2, '.', ''),
    ]);

    // ========== 3) POR MÉTODO ==========
    fputcsv($out, []);
    fputcsv($out, ["RESUMEN POR MÉTODO"]);
    fputcsv($out, ["Método", "# Ventas", "Total"]);

    foreach ($porMetodo as $r) {
        fputcsv($out, [
            $r->metodo,
            $r->ventas,
            number_format($r->total ?? 0, 2, '.', ''),
        ]);
    }
    // Totales por método
    fputcsv($out, [
        'TOTAL',
        $totalesMetodo['ventas'],
        number_format($totalesMetodo['total'] ?? 0, 2, '.', ''),
    ]);

    fclose($out);
};


        return response()->stream($callback, 200, $headers);
    }




public function exportExcel(Request $request)
{
    // Los mismos filtros que usas en index/exportCsv
    $start  = $request->input('start');
    $end    = $request->input('end');
    $userId = $request->input('user_id');
    $payment = $request->input('payment');

    if (!$start || !$end) {
        $hoy = \Illuminate\Support\Carbon::today();
        $start = $start ?: $hoy->format('Y-m-d');
        $end   = $end   ?: $hoy->format('Y-m-d');
    }

    $startDt = \Illuminate\Support\Carbon::parse($start)->startOfDay();
    $endDt   = \Illuminate\Support\Carbon::parse($end)->endOfDay();

    $base = \DB::table('sales as s')
        ->leftJoin('users as u', 'u.id', '=', 's.user_id')
        ->whereBetween('s.created_at', [$startDt, $endDt]);

    if ($userId)  $base->where('s.user_id', $userId);
    if ($payment) $base->where('s.payment', $payment);

    $porDia = (clone $base)
        ->selectRaw("
            DATE(s.created_at) as fecha,
            COUNT(*) as ventas,
            SUM(s.total) as total,
            SUM(CASE WHEN s.payment = 'cash' THEN s.total ELSE 0 END) as efectivo,
            SUM(CASE WHEN s.payment = 'card' THEN s.total ELSE 0 END) as tarjeta,
            SUM(CASE WHEN s.payment = 'transfer' THEN s.total ELSE 0 END) as transferencia,
            SUM(CASE WHEN s.payment = 'credit' THEN s.total ELSE 0 END) as credito
        ")
        ->groupByRaw('DATE(s.created_at)')
        ->orderBy('fecha', 'asc')
        ->get();

    $porUsuario = (clone $base)
        ->selectRaw("
            COALESCE(u.name, 'Desconocido') as usuario,
            s.user_id as user_id,
            COUNT(*) as ventas,
            SUM(s.total) as total,
            SUM(CASE WHEN s.payment = 'cash' THEN s.total ELSE 0 END) as efectivo,
            SUM(CASE WHEN s.payment = 'card' THEN s.total ELSE 0 END) as tarjeta,
            SUM(CASE WHEN s.payment = 'transfer' THEN s.total ELSE 0 END) as transferencia,
            SUM(CASE WHEN s.payment = 'credit' THEN s.total ELSE 0 END) as credito
        ")
        ->groupBy('s.user_id', 'u.name')
        ->orderBy('usuario', 'asc')
        ->get();

    $porMetodo = (clone $base)
        ->selectRaw("
            s.payment as metodo,
            COUNT(*) as ventas,
            SUM(s.total) as total
        ")
        ->groupBy('s.payment')
        ->orderBy('metodo', 'asc')
        ->get();

    $export = new ReporteVentasExport($porDia, $porUsuario, $porMetodo, $start, $end);

    $filename = "reporte_ventas_{$start}_{$end}.xlsx";
    return Excel::download($export, $filename);
}




}

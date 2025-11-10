<?php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\Sale;
use App\Models\ClientPayment;
use Illuminate\Support\Facades\Response;

class ReporteCxCController extends Controller
{
    /**
     * Listado con filtros + totales.
     * Filtro por nombre (q) y rango de fechas (from, to).
     * El rango se aplica tanto a ventas a crédito como a abonos.
     */
    public function index(Request $request)
    {
        $q     = trim((string) $request->get('q'));
        $from  = $request->get('from'); // YYYY-MM-DD
        $to    = $request->get('to');   // YYYY-MM-DD

        // Sumatorias por cliente usando withSum/withMax
        $clients = Client::query()
            ->when($q, fn ($qq) =>
                $qq->where(function($w) use ($q){
                    $w->where('name', 'like', "%{$q}%")
                      ->orWhere('phone', 'like', "%{$q}%");
                })
            )
            // Total vendido a crédito (sales.total) en rango
           ->withSum(['sales as total_credit' => function ($rel) use ($from, $to) {
    $values = ['credit','credito','crédito'];
    $placeholders = implode(',', array_fill(0, count($values), '?'));
    $rel->whereNotNull('client_id')
        ->whereRaw('LOWER(payment) IN ('.$placeholders.')', $values);
    if ($from) { $rel->whereDate('created_at', '>=', $from); }
    if ($to)   { $rel->whereDate('created_at', '<=', $to); }
}], 'total')


            // Total abonado (client_payments.amount) en rango
            ->withSum(['payments as total_paid' => function ($rel) use ($from, $to) {
                if ($from) { $rel->whereDate('created_at', '>=', $from); }
                if ($to)   { $rel->whereDate('created_at', '<=', $to); }
            }], 'amount')

            // Último abono (fecha)
            ->withMax(['payments as last_payment_at' => function ($rel) use ($from, $to) {
                if ($from) { $rel->whereDate('created_at', '>=', $from); }
                if ($to)   { $rel->whereDate('created_at', '<=', $to); }
            }], 'created_at')

            ->orderBy('name')
            ->get()
            // Calculamos saldo y filtramos si no hay deuda (para limpiar la vista)
            ->map(function ($c) {
                $credit = (float) ($c->total_credit ?? 0);
                $paid   = (float) ($c->total_paid ?? 0);
                $c->saldo = max(0, $credit - $paid);
                return $c;
            })
            ->filter(fn ($c) => $c->saldo > 0)
            ->values();

        // Total general de saldos mostrados
        $total_saldos = $clients->sum('saldo');

        return view('reportes.cxc', [
            'clients'      => $clients,
            'total_saldos' => $total_saldos,
            'filters'      => ['q'=>$q, 'from'=>$from, 'to'=>$to],
        ]);
    }

    /**
     * Detalle por cliente: ventas a crédito y abonos del rango.
     */
    public function show(Client $client, Request $request)
    {

        
        $from = $request->get('from');
        $to   = $request->get('to');



      $values = ['credit','credito','crédito'];
$ventasCredito = $client->sales()
    ->whereNotNull('client_id')
    ->whereRaw('LOWER(payment) IN ('.implode(',', array_fill(0, count($values), '?')).')', $values)
            ->when($from, fn($q)=>$q->whereDate('created_at','>=',$from))
            ->when($to,   fn($q)=>$q->whereDate('created_at','<=',$to))
            ->orderByDesc('created_at')
            ->get(['id','total','created_at']);

        $abonos = $client->payments()
            ->when($from, fn($q)=>$q->whereDate('created_at','>=',$from))
            ->when($to,   fn($q)=>$q->whereDate('created_at','<=',$to))
            ->orderByDesc('created_at')
            ->get(['id','amount','method','created_at']);

        $total_credito = (float) $ventasCredito->sum('total');
        $total_abonos  = (float) $abonos->sum('amount');
        $saldo         = max(0, $total_credito - $total_abonos);

        return view('reportes.cxc_show', compact(
            'client','ventasCredito','abonos','total_credito','total_abonos','saldo','from','to'
        ));
    }

    /**
     * Exportación rápida a CSV (no requiere paquetes).
     * Usa los mismos filtros del index.
     */
    public function exportCsv(Request $request)
    {
        // Reutilizamos la lógica del index de forma compacta
        $request->merge(['q' => trim((string) $request->get('q'))]);
        $dataView = $this->index($request)->getData();
        $clients = collect($dataView['clients']);

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="reporte_cxc.csv"',
        ];

        $callback = function () use ($clients) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Cliente','Teléfono','Total crédito','Total abonado','Saldo pendiente','Último abono']);
            foreach ($clients as $c) {
                fputcsv($out, [
                    $c->name,
                    $c->phone,
                    number_format($c->total_credit ?? 0, 2, '.', ''),
                    number_format($c->total_paid ?? 0, 2, '.', ''),
                    number_format($c->saldo ?? 0, 2, '.', ''),
                    $c->last_payment_at ?: '',
                ]);
            }
            fclose($out);
        };

        return Response::stream($callback, 200, $headers);
    }
}

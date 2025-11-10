<?php

namespace App\Http\Controllers;

use App\Http\Requests\Shifts\OpenShiftRequest;
use App\Http\Requests\Shifts\CloseShiftRequest;
use App\Models\CashShift;
use App\Models\Sale;
use App\Models\ClientPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\SaleItem;

class ShiftController extends Controller
{
    // Turno abierto del usuario autenticado (JSON)
    public function current(Request $request)
    {
        $shift = CashShift::openForUser($request->user()->id)->first();

        return response()
            ->json([
                'shift' => $shift ? [
                    'id'            => $shift->id,
                    'opened_at'     => $shift->opened_at->timezone(config('app.timezone'))->toDateTimeString(),
                    'opening_float' => (float) $shift->opening_float,
                ] : null
            ])
            ->header('Cache-Control','no-store, no-cache, must-revalidate, max-age=0');
    }

    // Abrir turno
    public function open(OpenShiftRequest $request)
    {
        $user = $request->user();

        // Regla: no permitir 2 turnos abiertos
        if (CashShift::openForUser($user->id)->exists()) {
            return response()->json(['message' => 'Ya tienes un turno abierto.'], 422);
        }

        $shift = CashShift::create([
            'user_id'       => $user->id,
            'opened_at'     => Carbon::now(),
            'opening_float' => $request->float('opening_float'),
            'notes'         => $request->string('notes'),
        ]);

        return response()->json([
            'message'  => 'Turno abierto',
            'shift_id' => $shift->id,
        ]);
    }

    // Resumen del turno abierto (o por ID)
   public function summary(Request $request, int $id = null)
{
    $uid = $request->user()->id;

    // Obtener el turno actual o por ID
    $shift = $id
        ? CashShift::where('user_id', $uid)->find($id)
        : CashShift::openForUser($uid)->first();

    if (!$shift) {
        return response()->json(['message' => 'No hay turno abierto.'], 404);
    }

    // ============================================
    // 1. CALCULAR VENTAS POR MÉTODO DE PAGO
    // ============================================
    $ventasQuery = Sale::where('user_id', $uid)
        ->when($shift->opened_at,  fn($q) => $q->where('created_at', '>=', $shift->opened_at))
        ->when($shift->closed_at,  fn($q) => $q->where('created_at', '<',  $shift->closed_at));

    $ventas = (clone $ventasQuery)
        ->selectRaw('payment, SUM(total) as total')
        ->groupBy('payment')
        ->get()
        ->keyBy('payment');

    $by_payment = $ventas->map(fn($r) => ['total' => (float)$r->total])->toArray();

    // ============================================
    // 2. CALCULAR DEVOLUCIONES (items negativos)
    // ============================================
    $devolucionesTotal = SaleItem::whereHas('sale', function($q) use ($uid, $shift) {
            $q->where('user_id', $uid);
            if ($shift->opened_at) {
                $q->where('created_at', '>=', $shift->opened_at);
            }
            if ($shift->closed_at) {
                $q->where('created_at', '<', $shift->closed_at);
            }
        })
        ->where('qty', '<', 0)  // ← Solo cantidades negativas (devoluciones)
        ->sum('total');  // Suma los totales negativos

    // Convertir a valor absoluto
    $devolucionesTotal = abs((float)$devolucionesTotal);

    // ============================================
    // 3. CALCULAR EFECTIVO ESPERADO
    // ============================================
    $cash_total = isset($ventas['cash']) ? (float)$ventas['cash']->total : 0.0;
    
    // Sumar abonos en efectivo
    $cashClientPayments = (float) ClientPayment::where('cash_shift_id', $shift->id)
        ->where('method', 'efectivo')
        ->sum('amount');

    // EFECTIVO ESPERADO = Fondo + Ventas cash + Abonos - Devoluciones
    $expected_cash = (float)($shift->opening_float ?? 0) + $cash_total + $cashClientPayments - $devolucionesTotal;

    // ============================================
    // 4. CALCULAR ABONOS POR MÉTODO
    // ============================================
    $abonos = ClientPayment::where('cash_shift_id', $shift->id)
        ->selectRaw('method, SUM(amount) as total')
        ->groupBy('method')
        ->get()
        ->keyBy('method');

    $abonos_by_method = [
        'efectivo'      => isset($abonos['efectivo'])      ? (float)$abonos['efectivo']->total      : 0.0,
        'tarjeta'       => isset($abonos['tarjeta'])       ? (float)$abonos['tarjeta']->total       : 0.0,
        'transferencia' => isset($abonos['transferencia']) ? (float)$abonos['transferencia']->total : 0.0,
    ];
    $abonos_total = array_sum($abonos_by_method);

    // ============================================
    // 5. RESPUESTA CON DEVOLUCIONES
    // ============================================
    return response()->json([
        'by_payment'        => $by_payment,
        'expected_cash'     => round($expected_cash, 2),
        'abonos_by_method'  => $abonos_by_method,
        'abonos_total'      => round($abonos_total, 2),
        'devoluciones'      => round($devolucionesTotal, 2),  // ← NUEVO
    ]);
}

    // Cerrar turno
    public function close(CloseShiftRequest $request)
{
    $user  = $request->user();
    $shift = CashShift::openForUser($user->id)->first();

    if (! $shift) {
        return response()->json(['message' => 'No tienes turno abierto.'], 422);
    }

    // ============================================
    // 1. CALCULAR VENTAS EN EFECTIVO
    // ============================================
    $salesCashTotal = (float) Sale::where('cash_shift_id', $shift->id)
        ->where('payment', 'cash')
        ->sum('total');

    // ============================================
    // 2. CALCULAR DEVOLUCIONES
    // ============================================
    $devolucionesTotal = SaleItem::whereHas('sale', function($q) use ($shift) {
            $q->where('cash_shift_id', $shift->id);
        })
        ->where('qty', '<', 0)
        ->sum('total');

    $devolucionesTotal = abs((float)$devolucionesTotal);

    // ============================================
    // 3. CALCULAR ABONOS EN EFECTIVO
    // ============================================
    $cashClientPayments = (float) ClientPayment::where('cash_shift_id', $shift->id)
        ->where('method', 'efectivo')
        ->sum('amount');

    // ============================================
    // 4. EFECTIVO ESPERADO
    // ============================================
    $expected = (float) $shift->opening_float + $salesCashTotal + $cashClientPayments - $devolucionesTotal;

    // ============================================
    // 5. ABONOS POR MÉTODO
    // ============================================
    $abonosClose = ClientPayment::where('cash_shift_id', $shift->id)
        ->selectRaw('method, SUM(amount) as total')
        ->groupBy('method')
        ->pluck('total','method')
        ->toArray();

    $abonos_by_method = [
        'efectivo'      => (float)($abonosClose['efectivo'] ?? 0),
        'tarjeta'       => (float)($abonosClose['tarjeta'] ?? 0),
        'transferencia' => (float)($abonosClose['transferencia'] ?? 0),
    ];
    $abonos_total = array_sum($abonos_by_method);

    // ============================================
    // 6. CALCULAR DIFERENCIA
    // ============================================
    $closing  = (float) $request->float('closing_cash_count');
    $diff     = $closing - $expected;

    // ============================================
    // 7. ACTUALIZAR TURNO
    // ============================================
    $shift->update([
        'closed_at'          => now(),
        'closing_cash_count' => $closing,
        'expected_cash'      => $expected,
        'difference'         => $diff,
        'notes'              => $request->string('notes'),
    ]);

    // ============================================
    // 8. RESPUESTA
    // ============================================
    return response()->json([
        'message'            => 'Turno cerrado',
        'expected_cash'      => $expected,
        'closing_cash_count' => $closing,
        'difference'         => $diff,
        'abonos_by_method'   => $abonos_by_method,
        'abonos_total'       => round($abonos_total, 2),
        'devoluciones'       => round($devolucionesTotal, 2),  // ← NUEVO
    ]);
}
}

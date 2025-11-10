<?php

namespace App\Http\Controllers;

use App\Http\Requests\Shifts\OpenShiftRequest;
use App\Http\Requests\Shifts\CloseShiftRequest;
use App\Models\CashShift;
use App\Models\Sale;
use App\Models\ClientPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;


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

        // turno actual del usuario (o por id si lo mandan)
        $shift = $id
            ? CashShift::where('user_id', $uid)->find($id)
            : CashShift::openForUser($uid)->first();

        if (!$shift) {
            return response()->json(['message' => 'No hay turno abierto.'], 404);
        }

        // ventas dentro de la ventana del turno, usando created_at
        $ventas = Sale::query()
            ->where('user_id', $uid)
            ->when($shift->opened_at,  fn($q) => $q->where('created_at', '>=', $shift->opened_at))
            ->when($shift->closed_at,  fn($q) => $q->where('created_at', '<',  $shift->closed_at))
            ->selectRaw('payment, SUM(total) as total')
            ->groupBy('payment')
            ->get()
            ->keyBy('payment');

        $by_payment = $ventas->map(fn($r) => ['total' => (float)$r->total])->toArray();

        // efectivo esperado = fondo inicial + ventas en efectivo del turno
        $cash_total     = isset($ventas['cash']) ? (float)$ventas['cash']->total : 0.0;
        $expected_cash  = (float)($shift->opening_float ?? 0) + $cash_total;

        // === CxC: sumar abonos en EFECTIVO ligados a este turno (para expected_cash) ===
        $cashClientPayments = (float) ClientPayment::where('cash_shift_id', $shift->id)
            ->where('method', 'efectivo')
            ->sum('amount');

        $expected_cash += $cashClientPayments;

        // --- ABONOS por método en este turno (solo para mostrar en UI) ---
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

        return response()->json([
            'by_payment'        => $by_payment,
            'expected_cash'     => round($expected_cash, 2),
            'abonos_by_method'  => $abonos_by_method,
            'abonos_total'      => round($abonos_total, 2),
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

        // expected_cash = opening_float + sum(ventas cash del turno)
        $salesCashTotal = (float) Sale::where('cash_shift_id', $shift->id)
            ->where('payment', 'cash')
            ->sum('total');

        $expected = (float) $shift->opening_float + $salesCashTotal;

        // === CxC: sumar abonos en EFECTIVO del turno ===
        $cashClientPayments = (float) ClientPayment::where('cash_shift_id', $shift->id)
            ->where('method', 'efectivo')
            ->sum('amount');

        $expected += $cashClientPayments;

        // (Opcional) Abonos por método para mostrar en la respuesta
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

        $closing  = (float) $request->float('closing_cash_count');
        $diff     = $closing - $expected;

        $shift->update([
            'closed_at'          => now(),
            'closing_cash_count' => $closing,
            'expected_cash'      => $expected,
            'difference'         => $diff,
            'notes'              => $request->string('notes'),
        ]);

        return response()->json([
            'message'            => 'Turno cerrado',
            'expected_cash'      => $expected,
            'closing_cash_count' => $closing,
            'difference'         => $diff,
            'abonos_by_method'   => $abonos_by_method,
            'abonos_total'       => round($abonos_total, 2),
        ]);
    }
}

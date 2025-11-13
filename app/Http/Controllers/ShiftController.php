<?php

namespace App\Http\Controllers;

use App\Http\Requests\Shifts\OpenShiftRequest;
use App\Http\Requests\Shifts\CloseShiftRequest;
use App\Models\CashShift;
use App\Models\Sale;
use App\Models\ClientPayment;
use App\Models\CashMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\SaleItem;

class ShiftController extends Controller
{
    /**
     * Obtener turno abierto del usuario autenticado
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * Abrir nuevo turno para el usuario autenticado
     *
     * @param OpenShiftRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * Obtener resumen del turno abierto actual o por ID
     *
     * @param Request $request
     * @param int|null $id ID del turno (opcional, si no se proporciona usa el turno abierto)
     * @return \Illuminate\Http\JsonResponse
     */
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

        // Calcular ventas por método de pago
        $ventasQuery = Sale::where('user_id', $uid)
            ->when($shift->opened_at,  fn($q) => $q->where('created_at', '>=', $shift->opened_at))
            ->when($shift->closed_at,  fn($q) => $q->where('created_at', '<',  $shift->closed_at));

        $ventas = (clone $ventasQuery)
            ->selectRaw('payment, SUM(total) as total')
            ->groupBy('payment')
            ->get()
            ->keyBy('payment');

        $by_payment = $ventas->map(fn($r) => ['total' => (float)$r->total])->toArray();
        $cash_total = isset($ventas['cash']) ? (float)$ventas['cash']->total : 0.0;

        // Usar métodos privados para evitar duplicación
        $devolucionesTotal = $this->calculateReturnsTotal($shift);
        $cashClientPayments = $this->calculateCashPayments($shift->id);
        $abonosData = $this->groupPaymentsByMethod($shift->id);

        // Calcular efectivo esperado = Fondo + Ventas cash + Abonos - Devoluciones
        $expected_cash = (float)($shift->opening_float ?? 0)
                       + $cash_total
                       + $cashClientPayments
                       - $devolucionesTotal;

        return response()->json([
            'by_payment'        => $by_payment,
            'expected_cash'     => round($expected_cash, 2),
            'abonos_by_method'  => [
                'efectivo'      => $abonosData['efectivo'],
                'tarjeta'       => $abonosData['tarjeta'],
                'transferencia' => $abonosData['transferencia'],
            ],
            'abonos_total'      => round($abonosData['total'], 2),
            'devoluciones'      => round($devolucionesTotal, 2),
        ]);
    }

    /**
     * Cerrar turno y calcular diferencia de caja
     *
     * @param CloseShiftRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function close(CloseShiftRequest $request)
    {
        $user  = $request->user();
        $shift = CashShift::openForUser($user->id)->first();

        if (! $shift) {
            return response()->json(['message' => 'No tienes turno abierto.'], 422);
        }

        // Calcular ventas en efectivo del turno
        $salesCashTotal = (float) Sale::where('cash_shift_id', $shift->id)
            ->where('payment', 'cash')
            ->sum('total');

        // Usar métodos privados para evitar duplicación
        $devolucionesTotal = $this->calculateReturnsTotal($shift);
        $cashClientPayments = $this->calculateCashPayments($shift->id);
        $abonosData = $this->groupPaymentsByMethod($shift->id);

        // Efectivo esperado = Fondo inicial + Ventas cash + Abonos - Devoluciones
        $expected = (float) $shift->opening_float
                  + $salesCashTotal
                  + $cashClientPayments
                  - $devolucionesTotal;

        // Calcular diferencia
        $closing = (float) $request->float('closing_cash_count');
        $diff = $closing - $expected;

        // Actualizar turno
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
            'abonos_by_method'   => [
                'efectivo'      => $abonosData['efectivo'],
                'tarjeta'       => $abonosData['tarjeta'],
                'transferencia' => $abonosData['transferencia'],
            ],
            'abonos_total'       => round($abonosData['total'], 2),
            'devoluciones'       => round($devolucionesTotal, 2),
        ]);
    }

    /**
     * Calcular total de devoluciones del turno
     *
     * @param CashShift $shift
     * @return float
     */
    private function calculateReturnsTotal(CashShift $shift): float
    {
        $total = SaleItem::whereHas('sale', function($q) use ($shift) {
                $q->where('cash_shift_id', $shift->id);
            })
            ->where('qty', '<', 0)
            ->sum('total');

        return abs((float)$total);
    }

    /**
     * Calcular abonos en efectivo del turno
     *
     * @param int $shiftId
     * @return float
     */
    private function calculateCashPayments(int $shiftId): float
    {
        return (float) ClientPayment::where('cash_shift_id', $shiftId)
            ->where('method', 'efectivo')
            ->sum('amount');
    }

    /**
     * Agrupar abonos por método de pago
     *
     * @param int $shiftId
     * @return array ['efectivo' => float, 'tarjeta' => float, 'transferencia' => float, 'total' => float]
     */
    private function groupPaymentsByMethod(int $shiftId): array
    {
        $abonos = ClientPayment::where('cash_shift_id', $shiftId)
            ->selectRaw('method, SUM(amount) as total')
            ->groupBy('method')
            ->pluck('total', 'method')
            ->toArray();

        $grouped = [
            'efectivo'      => (float)($abonos['efectivo'] ?? 0),
            'tarjeta'       => (float)($abonos['tarjeta'] ?? 0),
            'transferencia' => (float)($abonos['transferencia'] ?? 0),
        ];

        $grouped['total'] = array_sum($grouped);

        return $grouped;
    }

}

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

        // Reutilizar la lógica centralizada
        $summaryData = $this->calculateFullSummary($shift);

        return response()->json($summaryData);
    }

    /**
     * Cerrar turno y calcular diferencia de caja
     */
    public function close(CloseShiftRequest $request)
    {
        $user  = $request->user();
        $shift = CashShift::openForUser($user->id)->first();

        if (! $shift) {
            return response()->json(['message' => 'No tienes turno abierto.'], 422);
        }

        // Calcular resumen completo usando el método summary
        $summaryData = $this->calculateFullSummary($shift);

        // Calcular diferencia
        $closing = (float) $request->float('closing_cash_count');
        $expected = $summaryData['expected_cash'];
        $diff = $closing - $expected;

        // Actualizar turno
        $shift->update([
            'closed_at'          => now(),
            'closing_cash_count' => $closing,
            'expected_cash'      => $expected,
            'difference'         => $diff,
            'notes'              => $request->string('notes'),
            'affect_balance'     => $request->boolean('affect_balance', false),
        ]);

        // Registrar diferencia en balance si se marca la opción
        if ($request->boolean('affect_balance', false) && $diff != 0) {
            \App\Models\BalanceEntry::create([
                'date' => now()->toDateString(),
                'category' => $diff > 0 ? 'Sobrante de caja' : 'Faltante de caja',
                'type' => $diff > 0 ? 'ingreso' : 'gasto',
                'amount' => abs($diff),
                'description' => "Diferencia de caja - Turno #{$shift->id}",
                'shift_id' => $shift->id
            ]);
        }

        // ✅ DEVOLVER DATOS COMPLETOS PARA EL MODAL
        return response()->json([
            'ok' => true,
            'message' => 'Turno cerrado exitosamente',
            'shift' => [
                'id' => $shift->id,
                'opened_at' => $shift->opened_at->format('H:i'),
                'closed_at' => now()->format('H:i'),
                'opening_float' => (float) $shift->opening_float,
                'closing_cash_count' => $closing,
                'expected_cash' => $expected,
                'cash_difference' => $diff,
                'opening_notes' => $shift->notes,
                'closing_notes' => $request->string('notes'),
                'date' => $shift->opened_at->format('d/m/Y')
            ],
            'summary' => $summaryData,
            // 👉 URL a la página que muestra ventas por categoría de ese turno
            'redirect_url' => route('turnos.reporte', $shift->id),
        ]);
    }

    /**
     * Generar reporte de ventas por categoría del turno cerrado
     */
    public function report(int $id)
    {
        // ✅ CARGAR EL TURNO CON TODAS LAS RELACIONES NECESARIAS
        $shift = CashShift::with([
                'sales.items.product.category',  // Cargar categorías
                'user'                            // Cargar usuario
            ])
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        // Verificar que el turno esté cerrado
        if (!$shift->closed_at) {
            return redirect()->route('caja.pos')
                ->with('error', 'Este turno aún no ha sido cerrado.');
        }

        // ✅ AGRUPAR VENTAS POR CATEGORÍA - USANDO RELACIONES YA CARGADAS
        $salesByCategory = collect($shift->sales)  // ⚠️ SIN PARÉNTESIS
            ->flatMap(fn($sale) => $sale->items)
            ->filter(fn($item) => $item->product !== null)
            ->groupBy(function($item) {
                // Verificar que el producto y categoría existen
                if (!$item->product) {
                    return 'Sin categoría';
                }
                
                // Verificar que la categoría existe
                if ($item->product->category && is_object($item->product->category)) {
                    return $item->product->category->name;
                }
                
                // Fallback: Si tiene category_id pero no se cargó, intentar cargarla
                if ($item->product->category_id) {
                    $category = \App\Models\Category::find($item->product->category_id);
                    return $category ? $category->name : 'Sin categoría';
                }
                
                return 'Sin categoría';
            })
            ->map(function($items, $categoryName) {
                return [
                    'name' => $categoryName,
                    'qty' => $items->sum('qty'),
                    'total' => $items->sum('total'),
                    'products' => $items->groupBy('product_id')->map(function($productItems) {
                        $first = $productItems->first();
                        return [
                            'name' => $first->product ? $first->product->name : 'Producto desconocido',
                            'qty' => $productItems->sum('qty'),
                            'total' => $productItems->sum('total'),
                        ];
                    })->values()
                ];
            })
            ->sortByDesc('total')
            ->values();

        // Calcular resumen completo
        $summary = $this->calculateFullSummary($shift);

        return view('turnos.reporte', compact('shift', 'salesByCategory', 'summary'));
    }

    /**
     * Calcular resumen completo del turno (para reutilizar en close y summary)
     */
  /**
 * Calcular resumen completo del turno (para reutilizar en close y summary)
 */
private function calculateFullSummary(CashShift $shift): array
{
    $uid = $shift->user_id;

    // Calcular ventas por método de pago (SOLO ventas completadas, SIN devoluciones)
    $ventasQuery = Sale::where('user_id', $uid)
        ->when($shift->opened_at, fn($q) => $q->where('created_at', '>=', $shift->opened_at))
        ->when($shift->closed_at, fn($q) => $q->where('created_at', '<', $shift->closed_at));

    $ventas = (clone $ventasQuery)
        ->where('status', Sale::STATUS_COMPLETED) // ← ✅ LÍNEA AGREGADA
        ->selectRaw('payment, SUM(total) as total')
        ->groupBy('payment')
        ->get()
        ->keyBy('payment');

    $by_payment = $ventas->map(fn($r) => ['total' => (float)$r->total])->toArray();
    $cash_total = isset($ventas['cash']) ? (float)$ventas['cash']->total : 0.0;

    // Calcular componentes
    $devolucionesTotal = $this->calculateReturnsTotal($shift);
    $cashClientPayments = $this->calculateCashPayments($shift->id);
    $abonosData = $this->groupPaymentsByMethod($shift->id);
    $cashMovs = $this->calculateCashShiftMovements($shift->id);

    // Efectivo esperado (ahora correcto - resta devoluciones UNA SOLA VEZ)
    $expected_cash = (float)($shift->opening_float ?? 0)
        + $cash_total
        + $cashClientPayments
        + $cashMovs['ingresos']
        
        - $cashMovs['egresos'];

    return [
        'by_payment' => $by_payment,
        'expected_cash' => round($expected_cash, 2),
        'abonos_by_method' => [
            'efectivo' => $abonosData['efectivo'],
            'tarjeta' => $abonosData['tarjeta'],
            'transferencia' => $abonosData['transferencia'],
        ],
        'abonos_total' => round($abonosData['total'], 2),
        'devoluciones' => round($devolucionesTotal, 2),
        'cash_movements' => [
            'ingresos' => round($cashMovs['ingresos'], 2),
            'egresos' => round($cashMovs['egresos'], 2),
            'compras' => round($cashMovs['compras'], 2),
        ],
    ];
}
    /**
     * Calcular total de devoluciones del turno
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
     */
    private function calculateCashPayments(int $shiftId): float
    {
        return (float) ClientPayment::where('cash_shift_id', $shiftId)
            ->where('method', 'efectivo')
            ->sum('amount');
    }

    /**
     * Agrupar abonos por método de pago
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

    /**
     * Movimientos de caja del turno (solo EFECTIVO)
     */
    private function calculateCashShiftMovements(int $shiftId): array
    {
        // Verificar si la tabla cash_movements existe
        if (!\Schema::hasTable('cash_movements')) {
            return [
                'ingresos' => 0.0,
                'egresos'  => 0.0,
                'compras'  => 0.0,
            ];
        }

        $base = CashMovement::where('cash_shift_id', $shiftId)
            ->where('payment_method', 'efectivo');

        // Total usado para COMPRAS de inventario
        $compras = (clone $base)
            ->where('category', 'pago_proveedor')
            ->sum('amount');

        // Totales generales de ingresos/egresos
        $totals = $base->selectRaw('type, SUM(amount) as total')
            ->groupBy('type')
            ->pluck('total', 'type')
            ->toArray();

        return [
            'ingresos' => (float)($totals['ingreso'] ?? 0),
            'egresos'  => (float)($totals['egreso']  ?? 0),
            'compras'  => (float)$compras,
        ];
    }
}
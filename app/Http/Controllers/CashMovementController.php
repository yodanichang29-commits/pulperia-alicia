<?php

namespace App\Http\Controllers;

use App\Models\CashMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class CashMovementController extends Controller
{
    /**
     * Mostrar lista de movimientos con filtros, estadísticas y comparaciones
     *
     * Incluye: total de ingresos y egresos por categoría, comparación con mes anterior
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Obtener filtros
        $startDate = $request->filled('start') 
            ? Carbon::parse($request->start)->startOfDay()
            : Carbon::now()->startOfMonth();
        
        $endDate = $request->filled('end')
            ? Carbon::parse($request->end)->endOfDay()
            : Carbon::now()->endOfMonth();

        $type = $request->filled('type') ? $request->type : null;
        $category = $request->filled('category') ? $request->category : null;
        $paymentMethod = $request->filled('payment_method') ? $request->payment_method : null;

        // Query base con filtros
        $query = CashMovement::betweenDates($startDate, $endDate);

        if ($type) {
            $query->type($type);
        }

        if ($category) {
            $query->category($category);
        }

        if ($paymentMethod) {
            $query->paymentMethod($paymentMethod);
        }

        // Obtener movimientos ordenados por fecha (más reciente primero)
        $movements = $query->orderBy('date', 'desc')
                          ->orderBy('created_at', 'desc')
                          ->paginate(20);

        // ESTADÍSTICAS DEL PERÍODO
        // Total de ingresos
        $totalIngresos = CashMovement::betweenDates($startDate, $endDate)
            ->type('ingreso')
            ->sum('amount');

        // Total de egresos
        $totalEgresos = CashMovement::betweenDates($startDate, $endDate)
            ->type('egreso')
            ->sum('amount');

        // Ingresos por categoría (solo del período filtrado)
        $ingresosPorCategoria = CashMovement::betweenDates($startDate, $endDate)
            ->type('ingreso')
            ->selectRaw('
                COALESCE(custom_category, category) as cat,
                SUM(amount) as total
            ')
            ->groupBy('cat')
            ->orderBy('total', 'desc')
            ->get();

        // Egresos por categoría (solo del período filtrado)
        $egresosPorCategoria = CashMovement::betweenDates($startDate, $endDate)
            ->type('egreso')
            ->selectRaw('
                COALESCE(custom_category, category) as cat,
                SUM(amount) as total
            ')
            ->groupBy('cat')
            ->orderBy('total', 'desc')
            ->get();

        // COMPARACIÓN CON MES ANTERIOR
        $prevStartDate = Carbon::parse($startDate)->subMonth()->startOfMonth();
        $prevEndDate = Carbon::parse($startDate)->subMonth()->endOfMonth();

        $prevIngresos = CashMovement::betweenDates($prevStartDate, $prevEndDate)
            ->type('ingreso')
            ->sum('amount');

        $prevEgresos = CashMovement::betweenDates($prevStartDate, $prevEndDate)
            ->type('egreso')
            ->sum('amount');

        // Calcular porcentajes de cambio
        $ingresosChange = $prevIngresos > 0 
            ? (($totalIngresos - $prevIngresos) / $prevIngresos) * 100 
            : 0;

        $egresosChange = $prevEgresos > 0
            ? (($totalEgresos - $prevEgresos) / $prevEgresos) * 100
            : 0;

        // Obtener todas las categorías únicas para el filtro
        $allCategories = CashMovement::selectRaw('COALESCE(custom_category, category) as cat')
            ->distinct()
            ->orderBy('cat')
            ->pluck('cat')
            ->filter();

        return view('cash-movements.index', [
            'movements' => $movements,
            'totalIngresos' => $totalIngresos,
            'totalEgresos' => $totalEgresos,
            'ingresosPorCategoria' => $ingresosPorCategoria,
            'egresosPorCategoria' => $egresosPorCategoria,
            'prevIngresos' => $prevIngresos,
            'prevEgresos' => $prevEgresos,
            'ingresosChange' => $ingresosChange,
            'egresosChange' => $egresosChange,
            'allCategories' => $allCategories,
            'filters' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
                'type' => $type,
                'category' => $category,
                'payment_method' => $paymentMethod,
            ]
        ]);
    }

    /**
     * Mostrar formulario para crear nuevo movimiento de caja
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // Categorías predefinidas
        $categoriesIngreso = [
            'Préstamo recibido',
            'Inversión/aporte personal',
            'Venta de activo',
            'Devolución de proveedor',
            'Intereses ganados',
            'Donación recibida',
            'Otro',
        ];

        $categoriesEgreso = [
            'Agua',
            'Luz',
            'Alquiler',
            'Salarios',
            'Internet',
            'Gasolina',
            'Comida',
            'Medicamentos',
            'Consultas',
            'Otro',
        ];

        return view('cash-movements.create', [
            'categoriesIngreso' => $categoriesIngreso,
            'categoriesEgreso' => $categoriesEgreso,
        ]);
    }

    /**
     * Guardar nuevo movimiento de caja con validaciones y archivo opcional
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validar datos
        $validated = $request->validate([
            'date' => 'required|date',
            'type' => 'required|in:ingreso,egreso',
            'category' => 'required|string',
            'custom_category' => 'nullable|string|max:255',
            'description' => 'required|string|max:500',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:efectivo,transferencia,tarjeta,otro',
            'receipt_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB máximo
            'notes' => 'nullable|string|max:1000',
        ], [
            'date.required' => 'La fecha es obligatoria.',
            'type.required' => 'Debes seleccionar el tipo de movimiento.',
            'category.required' => 'La categoría es obligatoria.',
            'description.required' => 'La descripción es obligatoria.',
            'amount.required' => 'El monto es obligatorio.',
            'amount.min' => 'El monto debe ser mayor a 0.',
            'payment_method.required' => 'El método de pago es obligatorio.',
            'receipt_file.mimes' => 'El archivo debe ser PDF, JPG, JPEG o PNG.',
            'receipt_file.max' => 'El archivo no debe superar los 5MB.',
        ]);

        // Si seleccionó "Otro", usar custom_category
        if ($validated['category'] === 'Otro' && !empty($validated['custom_category'])) {
            $validated['category'] = null;
        } else {
            $validated['custom_category'] = null;
        }

        // Subir archivo si existe
        if ($request->hasFile('receipt_file')) {
            $file = $request->file('receipt_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('receipts', $filename, 'public');
            $validated['receipt_file'] = $path;
        }

        // Agregar usuario que lo creó
        $validated['created_by'] = Auth::id();

        // Crear el movimiento
        CashMovement::create($validated);

        return redirect()
            ->route('cash-movements.index')
            ->with('success', 'Movimiento registrado exitosamente.');
    }

    /**
     * Mostrar detalle de un movimiento de caja
     *
     * @param CashMovement $cashMovement
     * @return \Illuminate\View\View
     */
    public function show(CashMovement $cashMovement)
    {
        return view('cash-movements.show', [
            'movement' => $cashMovement,
        ]);
    }

    /**
     * Mostrar formulario para editar movimiento de caja
     *
     * @param CashMovement $cashMovement
     * @return \Illuminate\View\View
     */
    public function edit(CashMovement $cashMovement)
    {
        // Categorías predefinidas
        $categoriesIngreso = [
            'Préstamo recibido',
            'Inversión/aporte personal',
            'Venta de activo',
            'Devolución de proveedor',
            'Intereses ganados',
            'Donación recibida',
            'Otro',
        ];

        $categoriesEgreso = [
            'Agua',
            'Luz',
            'Alquiler',
            'Salarios',
            'Internet',
            'Gasolina',
            'Comida',
            'Medicamentos',
            'Consultas',
            'Otro',
        ];

        return view('cash-movements.edit', [
            'movement' => $cashMovement,
            'categoriesIngreso' => $categoriesIngreso,
            'categoriesEgreso' => $categoriesEgreso,
        ]);
    }

    /**
     * Actualizar movimiento de caja existente
     *
     * @param Request $request
     * @param CashMovement $cashMovement
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, CashMovement $cashMovement)
    {
        // Validar datos
        $validated = $request->validate([
            'date' => 'required|date',
            'type' => 'required|in:ingreso,egreso',
            'category' => 'required|string',
            'custom_category' => 'nullable|string|max:255',
            'description' => 'required|string|max:500',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:efectivo,transferencia,tarjeta,otro',
            'receipt_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Si seleccionó "Otro", usar custom_category
        if ($validated['category'] === 'Otro' && !empty($validated['custom_category'])) {
            $validated['category'] = null;
        } else {
            $validated['custom_category'] = null;
        }

        // Subir nuevo archivo si existe
        if ($request->hasFile('receipt_file')) {
            // Eliminar archivo anterior si existe
            if ($cashMovement->receipt_file) {
                Storage::disk('public')->delete($cashMovement->receipt_file);
            }

            $file = $request->file('receipt_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('receipts', $filename, 'public');
            $validated['receipt_file'] = $path;
        }

        // Actualizar el movimiento
        $cashMovement->update($validated);

        return redirect()
            ->route('cash-movements.show', $cashMovement)
            ->with('success', 'Movimiento actualizado exitosamente.');
    }

    /**
     * Eliminar movimiento de caja y su archivo asociado
     *
     * @param CashMovement $cashMovement
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(CashMovement $cashMovement)
    {
        // Eliminar archivo si existe
        if ($cashMovement->receipt_file) {
            Storage::disk('public')->delete($cashMovement->receipt_file);
        }

        $cashMovement->delete();

        return redirect()
            ->route('cash-movements.index')
            ->with('success', 'Movimiento eliminado exitosamente.');
    }
}
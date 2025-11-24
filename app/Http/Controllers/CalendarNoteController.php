<?php

namespace App\Http\Controllers;

use App\Models\CalendarNote;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CalendarNoteController extends Controller
{
    // Obtener notas de un mes específico
    public function index(Request $request)
    {
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);
        
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        
        // Usar user_id = 3 (el que veo en tu base de datos)
        $userId = 3;
        
        $notes = CalendarNote::whereBetween('date', [$startDate, $endDate])
            ->where('user_id', $userId)
            ->get()
            ->groupBy(function($note) {
                return $note->date->format('Y-m-d');
            });
        
        return response()->json($notes);
    }
    
    // Guardar una nueva nota
 public function store(Request $request)
{
    try {
        Log::info('Datos recibidos: ', $request->all());
        
        // Validar los datos
        $validated = $request->validate([
            'date' => 'required|date',
            'note' => 'required|string|max:500',
            'priority' => 'required|in:low,medium,high,urgent'
        ]);
        
        // Usar user_id = 3
        $userId = 3;
        
        // Crear la nota
        $note = CalendarNote::create([
            'date' => $validated['date'],
            'note' => $validated['note'],
            'priority' => $validated['priority'],
            'user_id' => $userId,
        ]);
        
        Log::info('Nota guardada: ', $note->toArray());
        
        return response()->json([
            'success' => true,
            'note' => $note
        ], 200);
        
    } catch (\Illuminate\Validation\ValidationException $e) {
        Log::error('Error de validación: ' . json_encode($e->errors()));
        
        return response()->json([
            'success' => false,
            'error' => 'Error de validación',
            'errors' => $e->errors()
        ], 422);
        
    } catch (\Exception $e) {
        Log::error('Error guardando nota: ' . $e->getMessage());
        Log::error('Stack trace: ' . $e->getTraceAsString());
        
        return response()->json([
            'success' => false,
            'error' => 'Error al guardar: ' . $e->getMessage()
        ], 500);
    }
}
    
    // Actualizar una nota existente
    public function update(Request $request, $id)
    {
        $userId = 3;
        
        $note = CalendarNote::where('user_id', $userId)->findOrFail($id);
        
        $validated = $request->validate([
            'note' => 'required|string|max:500',
            'priority' => 'required|in:low,medium,high,urgent'
        ]);
        
        $note->update($validated);
        
        return response()->json([
            'success' => true,
            'note' => $note
        ]);
    }
    
    // Eliminar una nota
    public function destroy($id)
    {
        $userId = 3;
        
        $note = CalendarNote::where('user_id', $userId)->findOrFail($id);
        $note->delete();
        
        return response()->json(['success' => true]);
    }
}
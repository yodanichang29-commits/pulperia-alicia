<?php

namespace App\Http\Controllers;

use App\Models\CalendarNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CalendarNoteController extends Controller
{
    /**
     * Obtener notas de un mes específico
     */
    public function getMonthNotes(Request $request, $year, $month)
    {
        $userId = Auth::id();

        $notes = CalendarNote::where('user_id', $userId)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get()
            ->map(function ($note) {
                return [
                    'id' => $note->id,
                    'date' => $note->date->format('Y-m-d'),
                    'note' => $note->note,
                    'priority' => $note->priority,
                    'priority_label' => $note->priority_label,
                    'priority_color' => $note->priority_color,
                    'updated_at' => $note->formatted_updated_at,
                ];
            });

        return response()->json($notes);
    }

    /**
     * Obtener nota de un día específico
     */
    public function getDayNote($date)
    {
        $userId = Auth::id();

        $note = CalendarNote::where('user_id', $userId)
            ->where('date', $date)
            ->first();

        if (!$note) {
            return response()->json(null);
        }

        return response()->json([
            'id' => $note->id,
            'date' => $note->date->format('Y-m-d'),
            'note' => $note->note,
            'priority' => $note->priority,
            'priority_label' => $note->priority_label,
            'priority_color' => $note->priority_color,
            'updated_at' => $note->formatted_updated_at,
        ]);
    }

    /**
     * Guardar o actualizar nota
     */
    public function saveNote(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'note' => 'nullable|string',
            'priority' => 'required|in:low,normal,important,urgent',
        ]);

        $userId = Auth::id();

        $note = CalendarNote::updateOrCreate(
            [
                'user_id' => $userId,
                'date' => $request->date,
            ],
            [
                'note' => $request->note,
                'priority' => $request->priority,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Nota guardada exitosamente',
            'note' => [
                'id' => $note->id,
                'date' => $note->date->format('Y-m-d'),
                'note' => $note->note,
                'priority' => $note->priority,
                'priority_label' => $note->priority_label,
                'priority_color' => $note->priority_color,
                'updated_at' => $note->formatted_updated_at,
            ],
        ]);
    }

    /**
     * Eliminar nota
     */
    public function deleteNote($date)
    {
        $userId = Auth::id();

        $note = CalendarNote::where('user_id', $userId)
            ->where('date', $date)
            ->first();

        if (!$note) {
            return response()->json([
                'success' => false,
                'message' => 'Nota no encontrada',
            ], 404);
        }

        $note->delete();

        return response()->json([
            'success' => true,
            'message' => 'Nota eliminada exitosamente',
        ]);
    }

    /**
     * Obtener información de prioridades disponibles
     */
    public function getPriorities()
    {
        return response()->json([
            'priorities' => array_map(function ($key) {
                return [
                    'value' => $key,
                    'label' => CalendarNote::getPriorityLabels()[$key],
                    'color' => CalendarNote::getPriorityColors()[$key],
                ];
            }, array_keys(CalendarNote::getPriorityColors())),
        ]);
    }
}

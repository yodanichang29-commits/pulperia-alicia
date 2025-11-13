<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalendarNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'note',
        'priority',
        'user_id',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Colores para cada prioridad
     */
    public static function getPriorityColors(): array
    {
        return [
            'low' => '#10b981',      // Verde
            'normal' => '#3b82f6',   // Azul
            'important' => '#f59e0b', // Amarillo/Naranja
            'urgent' => '#ef4444',   // Rojo
        ];
    }

    /**
     * Etiquetas en español para prioridades
     */
    public static function getPriorityLabels(): array
    {
        return [
            'low' => 'Baja',
            'normal' => 'Normal',
            'important' => 'Importante',
            'urgent' => 'Urgente',
        ];
    }

    /**
     * Relación con usuario
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtener color de la prioridad
     */
    public function getPriorityColorAttribute(): string
    {
        return self::getPriorityColors()[$this->priority] ?? '#6b7280';
    }

    /**
     * Obtener etiqueta de la prioridad
     */
    public function getPriorityLabelAttribute(): string
    {
        return self::getPriorityLabels()[$this->priority] ?? 'Normal';
    }

    /**
     * Formato de última actualización en AM/PM
     */
    public function getFormattedUpdatedAtAttribute(): string
    {
        return $this->updated_at->locale('es')->isoFormat('D/M/YYYY h:mm A');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CashMovement extends Model
{
    use HasFactory;

    /**
     * La tabla asociada con el modelo.
     */
    protected $table = 'cash_movements';

    /**
     * Los atributos que se pueden asignar masivamente.
     */
    protected $fillable = [
        'date',
        'type',
        'category',
        'custom_category',
        'description',
        'amount',
        'payment_method',
        'receipt_file',
        'notes',
        'created_by',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     */
    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    /**
     * Relación con el usuario que creó el movimiento.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Obtener la categoría final (predefinida o personalizada).
     */
    public function getFinalCategoryAttribute()
    {
        return $this->custom_category ?? $this->category;
    }

    /**
     * Verificar si es un ingreso.
     */
    public function isIngreso()
    {
        return $this->type === 'ingreso';
    }

    /**
     * Verificar si es un egreso.
     */
    public function isEgreso()
    {
        return $this->type === 'egreso';
    }

    /**
     * Obtener la URL del comprobante si existe.
     */
    public function getReceiptUrlAttribute()
    {
        if ($this->receipt_file) {
            return Storage::url($this->receipt_file);
        }
        return null;
    }

    /**
     * Scope para filtrar por tipo.
     */
    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope para filtrar por rango de fechas.
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope para filtrar por categoría.
     */
    public function scopeCategory($query, $category)
    {
        return $query->where(function($q) use ($category) {
            $q->where('category', $category)
              ->orWhere('custom_category', $category);
        });
    }

    /**
     * Scope para filtrar por método de pago.
     */
    public function scopePaymentMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }

    /**
     * Obtener etiqueta del tipo en español.
     */
    public function getTypeLabelAttribute()
    {
        return $this->type === 'ingreso' ? 'Ingreso' : 'Egreso';
    }

    /**
     * Obtener etiqueta del método de pago.
     */
    public function getPaymentMethodLabelAttribute()
    {
        $labels = [
            'efectivo' => 'Efectivo',
            'transferencia' => 'Transferencia bancaria',
            'tarjeta' => 'Tarjeta',
            'otro' => 'Otro',
        ];

        return $labels[$this->payment_method] ?? $this->payment_method;
    }

    /**
     * Obtener el color según el tipo (para la interfaz).
     */
    public function getTypeColorAttribute()
    {
        return $this->type === 'ingreso' ? 'emerald' : 'red';
    }

    /**
     * Obtener el ícono según el tipo.
     */
    public function getTypeIconAttribute()
    {
        return $this->type === 'ingreso' ? '✅' : '❌';
    }
}
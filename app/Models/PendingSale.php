<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendingSale extends Model
{
    // Campos que se pueden llenar (importante para seguridad)
    protected $fillable = [
        'user_id',
        'cash_shift_id',
        'items',
        'customer_name',
        'notes',
        'subtotal',
        'total',
    ];

    // Cómo Laravel debe manejar estos campos
    protected $casts = [
        'items' => 'array',      // JSON se convierte automáticamente en array
        'subtotal' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    // Relaciones con otras tablas
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cashShift()
    {
        return $this->belongsTo(CashShift::class);
    }
}
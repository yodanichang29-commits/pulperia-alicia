<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientPayment extends Model
{
    protected $fillable = [
        'client_id',
        'user_id',
        'cash_shift_id',
        'amount',
        'method',
        'notes'
    ];

    // Relación: este pago pertenece a un cliente
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    // Relación: pertenece a un usuario (quien lo cobró)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Relación: pertenece a un turno
    public function shift(): BelongsTo
    {
        return $this->belongsTo(CashShift::class, 'cash_shift_id');
    }
}

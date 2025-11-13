<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PurchasePayment extends Model
{
    protected $fillable = [
        'purchase_id',
        'amount',
        'payment_method',
        'affects_cash',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'affects_cash' => 'boolean',
    ];

    /**
     * Relación con la compra (inventory_transaction)
     */
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(InventoryTransaction::class, 'purchase_id');
    }

    /**
     * Usuario que registró el pago
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con el movimiento de caja (si afecta caja)
     */
    public function cashMovement(): HasOne
    {
        return $this->hasOne(CashMovement::class, 'purchase_payment_id');
    }

    /**
     * Etiquetas amigables para métodos de pago
     */
    public function getPaymentMethodLabelAttribute(): string
    {
        return match($this->payment_method) {
            'caja' => 'Efectivo de caja (sale del turno)',
            'efectivo_personal' => 'Efectivo personal (no sale del turno)',
            'credito' => 'A crédito',
            'transferencia' => 'Transferencia',
            'tarjeta' => 'Tarjeta',
            default => $this->payment_method,
        };
    }

    /**
     * Verificar si afecta la caja del turno
     */
    public function affectsCash(): bool
    {
        return $this->affects_cash === true;
    }

    /**
     * Verificar si es un pago en efectivo de caja
     */
    public function isCashPayment(): bool
    {
        return $this->payment_method === 'caja' && $this->affects_cash;
    }

    /**
     * Verificar si es un pago a crédito
     */
    public function isCreditPayment(): bool
    {
        return $this->payment_method === 'credito';
    }
}

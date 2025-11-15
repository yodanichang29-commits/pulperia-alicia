<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;      // ✅ este
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryTransaction extends Model
{
    protected $fillable = [
        'type','reason','moved_at', 'provider_id','supplier','reference','notes','user_id','total_cost',
        'paid_from_cash','paid_from_outside'
    ];

    protected $casts = [
        'moved_at' => 'date',
        'total_cost' => 'decimal:2',
        'paid_from_cash' => 'decimal:2',
        'paid_from_outside' => 'decimal:2',
    ];



    public function getReasonLabelAttribute(): string
    {
        $map = [
            'purchase'     => 'Compra',
            'adjust_in'    => 'Ajuste (+)',
            'waste'        => 'Merma',
            'damaged'      => 'Dañado',
            'expired'      => 'Vencido',
            'internal_use' => 'Uso interno',
            'adjust_out'   => 'Ajuste (-)',
        ];

        return $map[$this->reason] ?? str_replace('_', ' ', $this->reason);
    }

    /**
     * Calcula el total pagado (caja + externo)
     */
    public function getTotalPaidAttribute(): float
    {
        return (float)$this->paid_from_cash + (float)$this->paid_from_outside;
    }

    /**
     * Calcula el saldo pendiente (total - pagado)
     */
    public function getPendingBalanceAttribute(): float
    {
        return max(0, (float)$this->total_cost - $this->total_paid);
    }

    /**
     * Verifica si la compra está completamente pagada
     */
    public function getIsFullyPaidAttribute(): bool
    {
        return $this->pending_balance <= 0.01; // margen de error por decimales
    }



    // 🔹 ESTA es la relación que trae los renglones:
    public function items(): HasMany
    {
        return $this->hasMany(InventoryMovement::class, 'transaction_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    // quién la ANULÓ
    public function voider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by');
    }
}

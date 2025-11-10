<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    protected $fillable = [
        'user_id',
        'cash_shift_id',
        'datetime',
        'payment',
        'subtotal',
        'surcharge',
        'fee_pct',
        'total',
        'client_id',
        'due_date',
        'cash_received',
        'cash_change',
    ];


 public const PAYMENT_CREDIT = 'credit';



    protected $casts = [
        'datetime'      => 'datetime',
        'due_date'      => 'date',

        'subtotal'      => 'decimal:2',
        'surcharge'     => 'decimal:2',
        'fee_pct'       => 'decimal:2',
        'total'         => 'decimal:2',
        'cash_received' => 'decimal:2',
        'cash_change'   => 'decimal:2',
    ];

    // Relaciones
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cashShift(): BelongsTo
    {
        return $this->belongsTo(CashShift::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }




// ============================================
    // CAMPOS NUEVOS PARA DEVOLUCIONES
    // ============================================
    
    // Estados posibles
    const STATUS_COMPLETED = 'completed';
    const STATUS_PENDING   = 'pending';
    const STATUS_VOIDED    = 'voided';
    const STATUS_RETURNED  = 'returned';
    
    // Si es devolución, relación con la venta original
    public function originalSale()
    {
        return $this->belongsTo(Sale::class, 'original_sale_id');
    }
    
    // Devoluciones de esta venta
    public function returns()
    {
        return $this->hasMany(Sale::class, 'original_sale_id');
    }
    
    // Solo ventas completadas
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }
    
    // Solo ventas en espera
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }
    
    // ¿Se puede devolver esta venta?
    public function canBeReturned()
    {
        return $this->status === self::STATUS_COMPLETED 
            && $this->returns()->doesntExist();
    }



}

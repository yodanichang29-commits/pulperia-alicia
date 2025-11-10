<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    protected $fillable = [
         'transaction_id', 
        'product_id', 'type', 'qty',
        'before_qty', 'after_qty',
        'reason', 'user_id',
        'moved_at', 'supplier', 'reference',
        'unit_cost', 'total_cost',
    ];

    protected $casts = [
        'moved_at' => 'date',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

   
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }


   public function transaction(): BelongsTo
    {
        return $this->belongsTo(InventoryTransaction::class, 'transaction_id');
    }


}

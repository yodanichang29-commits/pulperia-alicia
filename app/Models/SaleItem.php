<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class SaleItem extends Model
{
    protected $fillable = ['sale_id','product_id','qty','price','total'];

    protected $casts = [
        'qty'   => 'decimal:2',
        'price' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function sale(){ return $this->belongsTo(Sale::class); }
}


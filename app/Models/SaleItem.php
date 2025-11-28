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


     // 👇 ESTA ES LA RELACIÓN QUE FALTA
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
        // si tu columna se llama distinto, cámbiala aquí
    }
}


<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Provider extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'contact_name', 'phone', 'email', 'notes', 'active'
    ];

    // Relación: un proveedor tiene muchos productos
  public function products()
{
    return $this->belongsToMany(\App\Models\Product::class, 'product_supplier')
        ->withPivot(['purchase_price', 'preferred'])
        ->withTimestamps();
}

}

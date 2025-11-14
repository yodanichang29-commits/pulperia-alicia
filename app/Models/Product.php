<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{



protected static function booted()
{
    static::saving(function ($product) {
        if ($product->purchase_price > 0 && ($product->cost == 0 || $product->cost === null)) {
            $product->cost = $product->purchase_price;
        }
    });
}

    
    use HasFactory;

    // Campos que se pueden guardar directamente
     protected $fillable = [
        'name', 'barcode', 'price', 'purchase_price', 'unit', 'photo',
        'expires_at', 'provider_id', 'category', 'stock', 'min_stock', 'active'
    ];

    // Relación con movimientos de inventario
    public function movements()
    {
        return $this->hasMany(\App\Models\InventoryMovement::class);
    }


public function getImageUrlAttribute()
{
    if ($this->photo && file_exists(public_path('storage/' . $this->photo))) {
        return asset('storage/' . $this->photo);
    }

    // Fallback si el symlink falla
    if ($this->photo && file_exists(public_path($this->photo))) {
        return asset($this->photo);
    }

    return asset('images/placeholder.png');
}



  public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    // (opcional) helpers para margen y ganancia
    public function getMarginPercentAttribute(): float
    {
        if ($this->price <= 0) return 0;
        return round((($this->price - $this->purchase_price) / $this->price) * 100, 2);
    }

    public function getProfitPerUnitAttribute(): float
    {
        return round(($this->price - $this->purchase_price), 2);
    }


    public function suppliers()
{
    return $this->belongsToMany(\App\Models\Provider::class, 'product_supplier')
        ->withPivot(['purchase_price', 'preferred'])
        ->withTimestamps();
}


}

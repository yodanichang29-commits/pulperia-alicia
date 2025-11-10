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
        'name', 'barcode', 'price', 'purchase_price', 'unit', 'image_path',
        'expires_at', 'provider_id', 'category', 'stock', 'min_stock', 'active'
    ];

    // Relación con movimientos de inventario
    public function movements()
    {
        return $this->hasMany(\App\Models\InventoryMovement::class);
    }


public function getImageUrlAttribute()
{
    if ($this->image_path && file_exists(public_path('storage/' . $this->image_path))) {
        return asset('storage/' . $this->image_path);
    }

    // Fallback si el symlink falla
    if ($this->image_path && file_exists(public_path($this->image_path))) {
        return asset($this->image_path);
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

}

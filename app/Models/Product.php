<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{



protected static function booted()
{
    static::saving(function ($product) {
        // El costo ya viene en $product->cost, no necesitamos hacer nada aquí
        // Este método se puede eliminar o dejar vacío
    });
}

    
    use HasFactory;

    // Campos que se pueden guardar directamente
     protected $fillable = [
        'name', 'barcode', 'price', 'cost', 'unit', 'photo',
        'category', 'stock', 'min_stock', 'expires_at', 'active'
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



    // (opcional) helpers para margen y ganancia
    public function getMarginPercentAttribute(): float
    {
        if ($this->price <= 0) return 0;
        return round((($this->price - $this->cost) / $this->price) * 100, 2);
    }

    public function getProfitPerUnitAttribute(): float
    {
        return round(($this->price - $this->cost), 2);
    }

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'active',
        'order'
    ];

    protected $casts = [
        'active' => 'boolean'
    ];

    // Relación: una categoría tiene muchos productos
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // Scope para obtener solo categorías activas
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    // Scope para ordenar por el campo 'order'
    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc')->orderBy('name', 'asc');
    }
}
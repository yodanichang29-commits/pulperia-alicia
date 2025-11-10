<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = ['name', 'phone'];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }


      public function payments()
    {
        return $this->hasMany(ClientPayment::class);
    }
}

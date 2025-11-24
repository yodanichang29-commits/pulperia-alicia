<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalendarNote extends Model
{
    protected $fillable = [
        'date',
        'note',
        'priority',
        'user_id',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
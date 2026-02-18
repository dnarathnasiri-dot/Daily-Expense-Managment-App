<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'category',
        'date',
        'description',
    ];

    protected $casts = [
        'amount' => 'float',
        'date'   => 'date:Y-m-d',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

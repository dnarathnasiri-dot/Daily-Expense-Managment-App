<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'email_notifications',
        'daily_summary',
        'budget_alerts',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_notifications' => 'boolean',
        'daily_summary'       => 'boolean',
        'budget_alerts'       => 'boolean',
    ];

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }
}

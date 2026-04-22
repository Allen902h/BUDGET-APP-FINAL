<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'profile_photo_path',
        'password',
        'currency_pref',
        'savings_goal_percentage',
        'monthly_budget_limit',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'monthly_budget_limit' => 'decimal:2',
        'savings_goal_percentage' => 'decimal:2',
    ];

    public function incomeCycles()
    {
        return $this->hasMany(IncomeCycle::class);
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function savingsGoals()
    {
        return $this->hasMany(SavingsGoal::class);
    }
}

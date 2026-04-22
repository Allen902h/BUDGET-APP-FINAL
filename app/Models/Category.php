<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'is_fixed',
        'budget_limit',
        'due_day',
        'last_alert_sent_at',
    ];

    protected $casts = [
        'is_fixed' => 'boolean',
        'budget_limit' => 'decimal:2',
        'due_day' => 'integer',
        'last_alert_sent_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}

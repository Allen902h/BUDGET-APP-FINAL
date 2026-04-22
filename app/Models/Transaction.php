<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'cycle_id',
        'category_id',
        'transaction_type',
        'amount',
        'timestamp',
        'note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'timestamp' => 'datetime',
        'note' => 'encrypted',
    ];

    public function cycle()
    {
        return $this->belongsTo(IncomeCycle::class, 'cycle_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}

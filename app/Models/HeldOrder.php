<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HeldOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'counter_id',
        'user_id',
        'hold_label',
        'customer_id',
        'cart_data',
        'held_at',
    ];

    protected function casts(): array
    {
        return [
            'cart_data' => 'array',
            'held_at' => 'datetime',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function counter(): BelongsTo
    {
        return $this->belongsTo(Counter::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}

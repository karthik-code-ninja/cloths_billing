<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'cash_shift_id',
        'payment_method',
        'amount',
        'reference_number',
        'is_verified',
        'received_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'is_verified' => 'boolean',
            'received_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function cashShift(): BelongsTo
    {
        return $this->belongsTo(CashShift::class);
    }
}

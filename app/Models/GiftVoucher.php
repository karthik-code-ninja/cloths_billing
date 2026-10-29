<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GiftVoucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'voucher_code',
        'initial_amount',
        'current_balance',
        'customer_id',
        'expiry_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'initial_amount' => 'decimal:2',
            'current_balance' => 'decimal:2',
            'expiry_date' => 'date',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function isUsable(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->expiry_date && now()->toDateString() > $this->expiry_date->toDateString()) {
            return false;
        }

        return (float) $this->current_balance > 0;
    }
}

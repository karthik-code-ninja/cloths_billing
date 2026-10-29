<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'city',
        'state',
        'state_code',
        'gstin',
        'customer_group',
        'loyalty_points',
        'store_credit_balance',
        'total_purchases_count',
        'total_spent',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'loyalty_points' => 'integer',
            'store_credit_balance' => 'decimal:2',
            'total_purchases_count' => 'integer',
            'total_spent' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function loyaltyTransactions(): HasMany
    {
        return $this->hasMany(LoyaltyTransaction::class);
    }

    public function giftVouchers(): HasMany
    {
        return $this->hasMany(GiftVoucher::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(ReturnOrder::class);
    }
}

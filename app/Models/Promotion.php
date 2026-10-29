<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'type', // percentage_discount, fixed_discount, bogo, quantity_tier
        'discount_value',
        'min_cart_amount',
        'min_quantity',
        'start_date',
        'end_date',
        'is_stackable',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2',
            'min_cart_amount' => 'decimal:2',
            'min_quantity' => 'integer',
            'start_date' => 'date',
            'end_date' => 'date',
            'is_stackable' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function isValidForCart(float $subtotal, int $itemCount): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $today = now()->toDateString();
        if ($this->start_date && $today < $this->start_date->toDateString()) {
            return false;
        }
        if ($this->end_date && $today > $this->end_date->toDateString()) {
            return false;
        }

        if ($subtotal < (float) $this->min_cart_amount) {
            return false;
        }

        if ($itemCount < $this->min_quantity) {
            return false;
        }

        return true;
    }
}

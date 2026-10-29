<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'company_name',
        'gstin',
        'phone',
        'email',
        'address',
        'city',
        'state',
        'state_code',
        'payment_terms_days',
        'opening_balance',
        'current_balance',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'payment_terms_days' => 'integer',
            'opening_balance' => 'decimal:2',
            'current_balance' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function goodsReceivedNotes(): HasMany
    {
        return $this->hasMany(GoodsReceivedNote::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SupplierPayment::class);
    }
}

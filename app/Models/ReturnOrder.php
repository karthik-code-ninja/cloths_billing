<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReturnOrder extends Model
{
    use HasFactory;

    protected $table = 'returns';

    protected $fillable = [
        'return_number',
        'original_order_id',
        'exchange_order_id',
        'customer_id',
        'branch_id',
        'user_id',
        'approved_by_id',
        'subtotal_returned',
        'tax_returned',
        'total_refund_amount',
        'refund_method',
        'reason',
        'status',
        'is_no_receipt',
        'return_date',
    ];

    protected function casts(): array
    {
        return [
            'subtotal_returned' => 'decimal:2',
            'tax_returned' => 'decimal:2',
            'total_refund_amount' => 'decimal:2',
            'is_no_receipt' => 'boolean',
            'return_date' => 'datetime',
        ];
    }

    public function originalOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'original_order_id');
    }

    public function exchangeOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'exchange_order_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReturnItem::class, 'return_id');
    }
}

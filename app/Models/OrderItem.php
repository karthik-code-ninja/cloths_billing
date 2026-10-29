<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_variant_id',
        'product_name',
        'variant_details',
        'hsn_code',
        'quantity',
        'unit_selling_price',
        'mrp',
        'unit_cost',
        'item_discount_amount',
        'item_discount_percent',
        'allocated_bill_discount',
        'tax_rate',
        'is_tax_inclusive',
        'taxable_amount',
        'cgst_amount',
        'sgst_amount',
        'igst_amount',
        'total_tax',
        'line_total',
        'returned_quantity',
    ];

    protected function casts(): array
    {
        return [
            'variant_details' => 'array',
            'quantity' => 'integer',
            'unit_selling_price' => 'decimal:2',
            'mrp' => 'decimal:2',
            'unit_cost' => 'decimal:2',
            'item_discount_amount' => 'decimal:2',
            'item_discount_percent' => 'decimal:2',
            'allocated_bill_discount' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'is_tax_inclusive' => 'boolean',
            'taxable_amount' => 'decimal:2',
            'cgst_amount' => 'decimal:2',
            'sgst_amount' => 'decimal:2',
            'igst_amount' => 'decimal:2',
            'total_tax' => 'decimal:2',
            'line_total' => 'decimal:2',
            'returned_quantity' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function returnItems(): HasMany
    {
        return $this->hasMany(ReturnItem::class, 'original_order_item_id');
    }

    public function getReturnableQuantityAttribute(): int
    {
        return max(0, (int) $this->quantity - (int) $this->returned_quantity);
    }

    public function getEffectiveUnitRefundPriceAttribute(): float
    {
        // Line total divided by quantity reflects original paid amount per unit
        if ($this->quantity <= 0) {
            return 0.00;
        }

        return round((float) $this->line_total / $this->quantity, 2);
    }
}

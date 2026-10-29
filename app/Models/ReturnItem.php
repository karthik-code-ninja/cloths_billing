<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturnItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'return_id',
        'original_order_item_id',
        'product_variant_id',
        'quantity',
        'unit_refund_price',
        'tax_rate',
        'tax_refund_amount',
        'line_refund_total',
        'condition', // resellable, damaged, defect
        'routed_location_id',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_refund_price' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'tax_refund_amount' => 'decimal:2',
            'line_refund_total' => 'decimal:2',
        ];
    }

    public function returnOrder(): BelongsTo
    {
        return $this->belongsTo(ReturnOrder::class, 'return_id');
    }

    public function originalOrderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class, 'original_order_item_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function routedLocation(): BelongsTo
    {
        return $this->belongsTo(StockLocation::class, 'routed_location_id');
    }
}

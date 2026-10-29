<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAdjustmentItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_adjustment_id',
        'product_variant_id',
        'system_quantity',
        'counted_quantity',
        'difference_quantity',
        'unit_cost',
    ];

    protected function casts(): array
    {
        return [
            'system_quantity' => 'integer',
            'counted_quantity' => 'integer',
            'difference_quantity' => 'integer',
            'unit_cost' => 'decimal:2',
        ];
    }

    public function adjustment(): BelongsTo
    {
        return $this->belongsTo(StockAdjustment::class, 'stock_adjustment_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}

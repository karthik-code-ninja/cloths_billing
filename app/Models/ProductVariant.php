<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'size',
        'color',
        'sku',
        'barcode',
        'purchase_cost',
        'selling_price',
        'mrp',
        'wholesale_price',
        'low_stock_threshold',
        'reorder_quantity',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'purchase_cost' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'mrp' => 'decimal:2',
            'wholesale_price' => 'decimal:2',
            'low_stock_threshold' => 'integer',
            'reorder_quantity' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(InventoryStock::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function getStockOnHandAttribute(): int
    {
        return (int) $this->stocks()->sum('quantity');
    }

    public function getStockAtLocation(?int $locationId = null): int
    {
        if (! $locationId) {
            return $this->stock_on_hand;
        }

        return (int) $this->stocks()->where('stock_location_id', $locationId)->value('quantity');
    }

    public function getFullNameAttribute(): string
    {
        $name = $this->product?->name ?? 'Item';

        return "{$name} - {$this->size} / {$this->color}";
    }

    public function hasSufficientStock(int $qty = 1, ?int $locationId = null): bool
    {
        return $this->getStockAtLocation($locationId) >= $qty;
    }
}

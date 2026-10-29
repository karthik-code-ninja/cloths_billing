<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'brand_id',
        'season_id',
        'name',
        'article_code',
        'hsn_code',
        'tax_rate',
        'is_tax_inclusive',
        'description',
        'gender',
        'fit',
        'fabric',
        'pattern',
        'image_path',
        'min_selling_price',
        'is_bundle',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'tax_rate' => 'decimal:2',
            'is_tax_inclusive' => 'boolean',
            'min_selling_price' => 'decimal:2',
            'is_bundle' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function activeVariants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->where('is_active', true);
    }

    public function bundles(): HasMany
    {
        return $this->hasMany(ProductBundle::class, 'bundle_product_id');
    }

    public function getTotalStockAttribute(): int
    {
        return (int) $this->variants()
            ->join('inventory_stocks', 'product_variants.id', '=', 'inventory_stocks.product_variant_id')
            ->sum('inventory_stocks.quantity');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GoodsReceivedNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'grn_number',
        'purchase_order_id',
        'supplier_id',
        'branch_id',
        'stock_location_id',
        'supplier_invoice_number',
        'invoice_date',
        'received_date',
        'total_amount',
        'paid_amount',
        'payment_status',
        'user_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'received_date' => 'date',
            'total_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
        ];
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(StockLocation::class, 'stock_location_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SupplierPayment::class);
    }
}

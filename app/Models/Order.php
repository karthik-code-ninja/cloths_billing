<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'order_type',
        'branch_id',
        'counter_id',
        'cash_shift_id',
        'customer_id',
        'cashier_id',
        'salesperson_id',
        'status',
        'idempotency_key',
        'subtotal',
        'item_discount_total',
        'bill_discount_total',
        'total_discount',
        'taxable_value',
        'tax_total',
        'cgst_total',
        'sgst_total',
        'igst_total',
        'round_off_amount',
        'grand_total',
        'paid_amount',
        'balance_amount',
        'change_amount',
        'is_credit_sale',
        'customer_snapshot',
        'tax_breakdown',
        'notes',
        'invoice_date',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'item_discount_total' => 'decimal:2',
            'bill_discount_total' => 'decimal:2',
            'total_discount' => 'decimal:2',
            'taxable_value' => 'decimal:2',
            'tax_total' => 'decimal:2',
            'cgst_total' => 'decimal:2',
            'sgst_total' => 'decimal:2',
            'igst_total' => 'decimal:2',
            'round_off_amount' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'balance_amount' => 'decimal:2',
            'change_amount' => 'decimal:2',
            'is_credit_sale' => 'boolean',
            'customer_snapshot' => 'array',
            'tax_breakdown' => 'array',
            'invoice_date' => 'datetime',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function counter(): BelongsTo
    {
        return $this->belongsTo(Counter::class);
    }

    public function cashShift(): BelongsTo
    {
        return $this->belongsTo(CashShift::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function salesperson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'salesperson_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(OrderPayment::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(ReturnOrder::class, 'original_order_id');
    }

    public function printJobs(): HasMany
    {
        return $this->hasMany(PrintJob::class);
    }

    public function isFullyPaid(): bool
    {
        return (float) $this->paid_amount >= (float) $this->grand_total;
    }

    public function getCustomerName(): string
    {
        if ($this->customer) {
            return $this->customer->name;
        }

        return $this->customer_snapshot['name'] ?? 'Walk-in Customer';
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashShift extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'counter_id',
        'user_id',
        'opened_at',
        'closed_at',
        'opening_float',
        'expected_closing_cash',
        'counted_closing_cash',
        'variance',
        'status',
        'notes',
        'closed_by_id',
    ];

    protected function casts(): array
    {
        return [
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
            'opening_float' => 'decimal:2',
            'expected_closing_cash' => 'decimal:2',
            'counted_closing_cash' => 'decimal:2',
            'variance' => 'decimal:2',
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

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function orderPayments(): HasMany
    {
        return $this->hasMany(OrderPayment::class);
    }

    public function cashTransactions(): HasMany
    {
        return $this->hasMany(CashTransaction::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function calculateExpectedCash(): float
    {
        $cashSales = (float) $this->orderPayments()
            ->where('payment_method', 'cash')
            ->sum('amount');

        $cashIn = (float) $this->cashTransactions()
            ->where('type', 'cash_in')
            ->sum('amount');

        $cashOut = (float) $this->cashTransactions()
            ->where('type', 'cash_out')
            ->sum('amount');

        $cashExpenses = (float) $this->expenses()
            ->where('payment_method', 'cash')
            ->sum('amount');

        return (float) $this->opening_float + $cashSales + $cashIn - $cashOut - $cashExpenses;
    }
}

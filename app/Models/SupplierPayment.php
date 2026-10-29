<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_number',
        'supplier_id',
        'goods_received_note_id',
        'amount',
        'payment_method',
        'reference_number',
        'payment_date',
        'notes',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function goodsReceivedNote(): BelongsTo
    {
        return $this->belongsTo(GoodsReceivedNote::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

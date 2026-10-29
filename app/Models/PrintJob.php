<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrintJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'printer_id',
        'order_id',
        'job_type',
        'payload_version',
        'payload',
        'status', // pending, submitted, success, failed
        'attempts',
        'error_message',
        'is_reprint',
        'printed_at',
    ];

    protected function casts(): array
    {
        return [
            'attempts' => 'integer',
            'is_reprint' => 'boolean',
            'printed_at' => 'datetime',
        ];
    }

    public function printer(): BelongsTo
    {
        return $this->belongsTo(Printer::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}

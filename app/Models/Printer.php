<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Printer extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'counter_id',
        'name',
        'connection_type', // browser, qz_tray, lan_escpos, mock
        'device_identifier',
        'ip_address',
        'port',
        'capability_profile', // epson_standard, xprinter_80, xprinter_58, generic_escpos
        'paper_width_mm', // 58, 80
        'columns',
        'dpi',
        'margins_mm',
        'feed_lines',
        'has_cutter',
        'has_cash_drawer',
        'is_default_receipt',
        'is_default_label',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'port' => 'integer',
            'paper_width_mm' => 'integer',
            'columns' => 'integer',
            'dpi' => 'integer',
            'margins_mm' => 'integer',
            'feed_lines' => 'integer',
            'has_cutter' => 'boolean',
            'has_cash_drawer' => 'boolean',
            'is_default_receipt' => 'boolean',
            'is_default_label' => 'boolean',
            'is_active' => 'boolean',
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

    public function printJobs(): HasMany
    {
        return $this->hasMany(PrintJob::class);
    }
}

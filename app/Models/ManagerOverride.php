<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManagerOverride extends Model
{
    use HasFactory;

    protected $fillable = [
        'manager_id',
        'cashier_id',
        'action_type',
        'reference_type',
        'reference_id',
        'threshold_value',
        'actual_value',
        'reason',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'threshold_value' => 'decimal:2',
            'actual_value' => 'decimal:2',
        ];
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }
}

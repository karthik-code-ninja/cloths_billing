<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'address',
        'city',
        'state',
        'state_code',
        'pincode',
        'phone',
        'email',
        'gstin',
        'is_main',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_main' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function counters(): HasMany
    {
        return $this->hasMany(Counter::class);
    }

    public function stockLocations(): HasMany
    {
        return $this->hasMany(StockLocation::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function printers(): HasMany
    {
        return $this->hasMany(Printer::class);
    }
}

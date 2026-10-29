<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'branch_id',
        'is_active',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'max_discount_percentage',
        'can_override_price',
        'can_give_refund',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'max_discount_percentage' => 'decimal:2',
            'can_override_price' => 'boolean',
            'can_give_refund' => 'boolean',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function cashShifts(): HasMany
    {
        return $this->hasMany(CashShift::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'cashier_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isManager(): bool
    {
        return in_array($this->role, ['admin', 'manager'], true);
    }

    public function isCashier(): bool
    {
        return $this->role === 'cashier';
    }

    public function isInventoryStaff(): bool
    {
        return $this->role === 'inventory_staff';
    }

    public function isAccountant(): bool
    {
        return $this->role === 'accountant';
    }

    public function canViewCostPrices(): bool
    {
        return $this->hasPermission('view_cost_prices');
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return match ($permission) {
            'view_cost_prices' => in_array($this->role, ['admin', 'manager', 'accountant'], true),
            'give_discount' => in_array($this->role, ['admin', 'manager', 'cashier'], true),
            'override_price' => in_array($this->role, ['admin', 'manager'], true) || $this->can_override_price,
            'process_refund' => in_array($this->role, ['admin', 'manager'], true) || $this->can_give_refund,
            'adjust_stock' => in_array($this->role, ['admin', 'manager', 'inventory_staff'], true),
            'manage_purchases' => in_array($this->role, ['admin', 'manager', 'inventory_staff'], true),
            'view_reports' => in_array($this->role, ['admin', 'manager', 'accountant'], true),
            'export_reports' => in_array($this->role, ['admin', 'manager', 'accountant'], true),
            'manage_settings' => $this->isAdmin(),
            'manage_printers' => in_array($this->role, ['admin', 'manager'], true),
            'manage_users' => $this->isAdmin(),
            default => false,
        };
    }

    public function canGiveDiscount(float $percentage): bool
    {
        if ($this->isAdmin() || $this->isManager()) {
            return true;
        }

        return $percentage <= (float) $this->max_discount_percentage;
    }
}

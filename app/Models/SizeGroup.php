<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SizeGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'sizes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sizes' => 'array',
            'is_active' => 'boolean',
        ];
    }
}

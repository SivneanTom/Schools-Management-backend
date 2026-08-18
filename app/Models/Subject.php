<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name_km',
        'name_en',
        'description_km',
        'description_en',
        'credit_hours',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'credit_hours' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}

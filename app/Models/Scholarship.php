<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Scholarship extends Model
{
    use HasFactory;

    public const TYPE_PERCENTAGE = 'PERCENTAGE';
    public const TYPE_FIXED = 'FIXED';

    protected $fillable = [
        'name_km',
        'name_en',
        'description_km',
        'description_en',
        'discount_type',
        'discount_value',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function studentScholarships(): HasMany
    {
        return $this->hasMany(StudentScholarship::class);
    }
}

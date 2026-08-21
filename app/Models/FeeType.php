<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeeType extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name_km',
        'name_en',
        'description_km',
        'description_en',
        'default_amount',
        'frequency',
        'is_active',
    ];

    protected $casts = [
        'default_amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function studentFees(): HasMany
    {
        return $this->hasMany(StudentFee::class);
    }
}

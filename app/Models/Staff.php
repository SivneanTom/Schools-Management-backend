<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Staff extends Model
{
    use HasFactory;

    protected $table = 'staff';

    protected $fillable = [
        'user_id',
        'staff_no',
        'full_name_km',
        'full_name_en',
        'phone',
        'hire_date',
        'position_title_km',
        'position_title_en',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'hire_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

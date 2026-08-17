<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ParentProfile extends Model
{
    use HasFactory;

    protected $table = 'parents';

    protected $fillable = [
        'user_id',
        'parent_code',
        'first_name_km',
        'last_name_km',
        'first_name_en',
        'last_name_en',
        'gender',
        'phone',
        'address_km',
        'address_en',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(
            Student::class,
            'student_parents',
            'parent_id',
            'student_id'
        )
            ->withPivot([
                'relationship',
                'is_primary',
            ])
            ->withTimestamps();
    }
}
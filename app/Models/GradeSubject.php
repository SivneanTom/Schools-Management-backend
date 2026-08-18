<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradeSubject extends Model
{
    use HasFactory;

    protected $fillable = [
        'grade_id',
        'subject_id',
        'credit_hours',
        'is_required',
    ];

    protected function casts(): array
    {
        return [
            'credit_hours' => 'integer',
            'is_required' => 'boolean',
        ];
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}

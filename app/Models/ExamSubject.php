<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamSubject extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id',
        'teacher_assignment_id',
        'exam_date',
        'start_time',
        'end_time',
        'max_score',
        'pass_score',
    ];

    protected function casts(): array
    {
        return [
            'exam_id' => 'integer',
            'teacher_assignment_id' => 'integer',
            'exam_date' => 'date:Y-m-d',
            'max_score' => 'decimal:2',
            'pass_score' => 'decimal:2',
        ];
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function teacherAssignment(): BelongsTo
    {
        return $this->belongsTo(TeacherAssignment::class);
    }

    public function examResults(): HasMany
    {
        return $this->hasMany(ExamResult::class);
    }
}

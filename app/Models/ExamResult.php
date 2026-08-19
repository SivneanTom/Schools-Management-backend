<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_subject_id',
        'student_id',
        'score',
        'grade',
        'remarks_km',
        'remarks_en',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'exam_subject_id' => 'integer',
            'student_id' => 'integer',
            'score' => 'decimal:2',
            'published_at' => 'datetime',
        ];
    }

    public function examSubject(): BelongsTo
    {
        return $this->belongsTo(ExamSubject::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}

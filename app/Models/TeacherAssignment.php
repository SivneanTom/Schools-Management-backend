<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'class_id',
        'subject_id',
        'semester_id',
        'assigned_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'date',
        ];
    }

    public function teacher()
    {
        return $this->belongsTo(
            Teacher::class,
            'teacher_id'
        );
    }

    public function schoolClass()
    {
        return $this->belongsTo(
            SchoolClass::class,
            'class_id'
        );
    }

    public function subject()
    {
        return $this->belongsTo(
            Subject::class,
            'subject_id'
        );
    }
}

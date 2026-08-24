<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Timetable extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_assignment_id',
        'room_id',
        'day_of_week',
        'start_time',
        'end_time',
    ];

    protected function casts(): array
    {
        return [
            'teacher_assignment_id' => 'integer',
            'room_id' => 'integer',
        ];
    }

    public function teacherAssignment()
    {
        return $this->belongsTo(
            TeacherAssignment::class,
            'teacher_assignment_id'
        );
    }

    public function room()
    {
        return $this->belongsTo(
            Room::class,
            'room_id'
        );
    }
}

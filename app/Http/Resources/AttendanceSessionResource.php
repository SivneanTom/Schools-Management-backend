<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceSessionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'teacher_assignment_id' => $this->teacher_assignment_id,

            'teacher_assignment' => $this->whenLoaded(
                'teacherAssignment',
                fn () => [
                    'id' => $this->teacherAssignment->id,
                    'teacher_id' => $this->teacherAssignment->teacher_id,
                    'class_id' => $this->teacherAssignment->class_id,
                    'subject_id' => $this->teacherAssignment->subject_id,
                    'semester_id' => $this->teacherAssignment->semester_id,
                    'status' => $this->teacherAssignment->status,
                ]
            ),

            'attendance_date' => $this->attendance_date?->format('Y-m-d'),
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'status' => $this->status,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

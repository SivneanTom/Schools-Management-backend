<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExamSubjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'exam_id' => $this->exam_id,
            'exam' => $this->whenLoaded('exam', fn () => [
                'id' => $this->exam->id,
                'semester_id' => $this->exam->semester_id,
                'name_km' => $this->exam->name_km,
                'name_en' => $this->exam->name_en,
                'exam_type' => $this->exam->exam_type,
                'start_date' => $this->exam->start_date?->format('Y-m-d'),
                'end_date' => $this->exam->end_date?->format('Y-m-d'),
                'status' => $this->exam->status,
            ]),
            'teacher_assignment_id' => $this->teacher_assignment_id,
            'teacher_assignment' => $this->whenLoaded('teacherAssignment', fn () => [
                'id' => $this->teacherAssignment->id,
                'teacher_id' => $this->teacherAssignment->teacher_id,
                'class_id' => $this->teacherAssignment->class_id,
                'subject_id' => $this->teacherAssignment->subject_id,
                'semester_id' => $this->teacherAssignment->semester_id,
                'status' => $this->teacherAssignment->status,
            ]),
            'exam_date' => $this->exam_date?->format('Y-m-d'),
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'max_score' => $this->max_score,
            'pass_score' => $this->pass_score,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssignmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'teacher_assignment_id' => $this->teacher_assignment_id,
            'teacher_assignment' => $this->whenLoaded('teacherAssignment', fn() => ['id' => $this->teacherAssignment->id, 'teacher_id' => $this->teacherAssignment->teacher_id, 'class_id' => $this->teacherAssignment->class_id, 'subject_id' => $this->teacherAssignment->subject_id, 'semester_id' => $this->teacherAssignment->semester_id, 'status' => $this->teacherAssignment->status]),
            'title_km' => $this->title_km,
            'title_en' => $this->title_en,
            'description_km' => $this->description_km,
            'description_en' => $this->description_en,
            'assigned_at' => $this->assigned_at?->toISOString(),
            'due_at' => $this->due_at?->toISOString(),
            'max_score' => $this->max_score,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}

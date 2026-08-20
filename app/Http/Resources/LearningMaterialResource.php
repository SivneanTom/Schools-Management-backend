<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LearningMaterialResource extends JsonResource
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
            'material_type' => $this->material_type,
            'file_url' => $this->file_url,
            'published_at' => $this->published_at?->toISOString(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}

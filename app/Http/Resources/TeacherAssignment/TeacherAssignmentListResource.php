<?php

namespace App\Http\Resources\TeacherAssignment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeacherAssignmentListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'teacher' => [
                'id' => $this->teacher?->id,
                'teacherCode' => $this->teacher?->teacher_code,
                'fullNameKm' => trim(($this->teacher?->first_name_km ?? '') . ' ' . ($this->teacher?->last_name_km ?? '')),
                'fullNameEn' => trim(($this->teacher?->first_name_en ?? '') . ' ' . ($this->teacher?->last_name_en ?? '')),
            ],
            'class' => [
                'id' => $this->schoolClass?->id,
                'nameKm' => $this->schoolClass?->name_km,
                'nameEn' => $this->schoolClass?->name_en,
                'grade' => [
                    'id' => $this->schoolClass?->grade?->id,
                    'code' => $this->schoolClass?->grade?->code,
                    'nameKm' => $this->schoolClass?->grade?->name_km,
                    'nameEn' => $this->schoolClass?->grade?->name_en,
                ],
            ],
            'subject' => [
                'id' => $this->subject?->id,
                'code' => $this->subject?->code,
                'nameKm' => $this->subject?->name_km,
                'nameEn' => $this->subject?->name_en,
            ],
            'semester' => [
                'id' => $this->semester?->id,
                'name' => $this->semester?->name,
            ],
            'assignedAt' => $this->assigned_at?->format('Y-m-d'),
            'status' => $this->status,
        ];
    }
}

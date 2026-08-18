<?php

namespace App\Http\Resources\TeacherAssignment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeacherAssignmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'teacher' => [
                'id' => $this->teacher?->id,
                'teacherCode' => $this->teacher?->teacher_code,
                'firstNameKm' => $this->teacher?->first_name_km,
                'lastNameKm' => $this->teacher?->last_name_km,
                'firstNameEn' => $this->teacher?->first_name_en,
                'lastNameEn' => $this->teacher?->last_name_en,
                'specialization' => $this->teacher?->specialization,
                'status' => $this->teacher?->status,
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
                'academicYear' => [
                    'id' => $this->schoolClass?->academicYear?->id,
                    'name' => $this->schoolClass?->academicYear?->name,
                ],
            ],
            'subject' => [
                'id' => $this->subject?->id,
                'code' => $this->subject?->code,
                'nameKm' => $this->subject?->name_km,
                'nameEn' => $this->subject?->name_en,
                'isActive' => $this->subject?->is_active,
            ],
            'semester' => [
                'id' => $this->semester?->id,
                'name' => $this->semester?->name,
                'startDate' => $this->semester?->start_date?->format('Y-m-d'),
                'endDate' => $this->semester?->end_date?->format('Y-m-d'),
                'status' => $this->semester?->status,
            ],
            'assignedAt' => $this->assigned_at?->format('Y-m-d'),
            'status' => $this->status,
            'createdAt' => $this->created_at?->toISOString(),
            'updatedAt' => $this->updated_at?->toISOString(),
        ];
    }
}

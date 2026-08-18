<?php

namespace App\Http\Resources\Enrollment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EnrollmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student' => [
                'id' => $this->student?->id,
                'studentCode' => $this->student?->student_code,
                'firstNameKm' => $this->student?->first_name_km,
                'lastNameKm' => $this->student?->last_name_km,
                'firstNameEn' => $this->student?->first_name_en,
                'lastNameEn' => $this->student?->last_name_en,
                'status' => $this->student?->status,
            ],
            'class' => [
                'id' => $this->schoolClass?->id,
                'nameKm' => $this->schoolClass?->name_km,
                'nameEn' => $this->schoolClass?->name_en,
                'capacity' => $this->schoolClass?->capacity,
                'status' => $this->schoolClass?->status,
                'grade' => [
                    'id' => $this->schoolClass?->grade?->id,
                    'code' => $this->schoolClass?->grade?->code,
                    'nameKm' => $this->schoolClass?->grade?->name_km,
                    'nameEn' => $this->schoolClass?->grade?->name_en,
                ],
            ],
            'academicYear' => [
                'id' => $this->academicYear?->id,
                'name' => $this->academicYear?->name,
                'startDate' => $this->academicYear?->start_date?->format('Y-m-d'),
                'endDate' => $this->academicYear?->end_date?->format('Y-m-d'),
                'status' => $this->academicYear?->status,
            ],
            'enrolledAt' => $this->enrolled_at?->format('Y-m-d'),
            'status' => $this->status,
            'createdAt' => $this->created_at?->toISOString(),
            'updatedAt' => $this->updated_at?->toISOString(),
        ];
    }
}

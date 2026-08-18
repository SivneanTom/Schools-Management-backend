<?php

namespace App\Http\Resources\Enrollment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EnrollmentListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student' => [
                'id' => $this->student?->id,
                'studentCode' => $this->student?->student_code,
                'fullNameKm' => trim(($this->student?->first_name_km ?? '') . ' ' . ($this->student?->last_name_km ?? '')),
                'fullNameEn' => trim(($this->student?->first_name_en ?? '') . ' ' . ($this->student?->last_name_en ?? '')),
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
            'academicYear' => [
                'id' => $this->academicYear?->id,
                'name' => $this->academicYear?->name,
            ],
            'enrolledAt' => $this->enrolled_at?->format('Y-m-d'),
            'status' => $this->status,
        ];
    }
}

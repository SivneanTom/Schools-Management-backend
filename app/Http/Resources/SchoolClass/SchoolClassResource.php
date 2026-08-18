<?php

namespace App\Http\Resources\SchoolClass;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SchoolClassResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'gradeId' => $this->grade_id,
            'academicYearId' => $this->academic_year_id,
            'homeroomTeacherId' => $this->homeroom_teacher_id,

            'nameKm' => $this->name_km,
            'nameEn' => $this->name_en,

            'capacity' => $this->capacity,
            'status' => $this->status,

            'grade' => [
                'id' => $this->grade?->id,
                'code' => $this->grade?->code,
                'nameKm' => $this->grade?->name_km,
                'nameEn' => $this->grade?->name_en,
            ],

            'academicYear' => [
                'id' => $this->academicYear?->id,
                'name' => $this->academicYear?->name,
                'startDate' => $this->academicYear?->start_date?->format('Y-m-d'),
                'endDate' => $this->academicYear?->end_date?->format('Y-m-d'),
                'status' => $this->academicYear?->status,
            ],

            'homeroomTeacher' => $this->homeroomTeacher
                ? [
                    'id' => $this->homeroomTeacher->id,
                    'teacherCode' => $this->homeroomTeacher->teacher_code,
                    'fullNameKm' => trim(
                        $this->homeroomTeacher->first_name_km . ' ' .
                        $this->homeroomTeacher->last_name_km
                    ),
                    'fullNameEn' => trim(
                        ($this->homeroomTeacher->first_name_en ?? '') . ' ' .
                        ($this->homeroomTeacher->last_name_en ?? '')
                    ),
                    'status' => $this->homeroomTeacher->status,
                ]
                : null,

            'createdAt' => $this->created_at?->toISOString(),
            'updatedAt' => $this->updated_at?->toISOString(),
        ];
    }
}

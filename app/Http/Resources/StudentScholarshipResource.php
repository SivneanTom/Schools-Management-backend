<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentScholarshipResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student' => $this->student ? [
                'id' => $this->student->id,
                'student_code' => $this->student->student_code,
                'full_name_km' => trim(
                    $this->student->first_name_km.' '.
                    $this->student->last_name_km
                ),
                'full_name_en' => trim(
                    $this->student->first_name_en.' '.
                    $this->student->last_name_en
                ),
            ] : null,
            'scholarship' => $this->scholarship ? [
                'id' => $this->scholarship->id,
                'name_km' => $this->scholarship->name_km,
                'name_en' => $this->scholarship->name_en,
                'discount_type' => $this->scholarship->discount_type,
                'discount_value' => number_format(
                    (float) $this->scholarship->discount_value,
                    2,
                    '.',
                    ''
                ),
                'start_date' => $this->scholarship->start_date
                    ?->toDateString(),
                'end_date' => $this->scholarship->end_date
                    ?->toDateString(),
            ] : null,
            'academic_year' => $this->academicYear ? [
                'id' => $this->academicYear->id,
                'name' => $this->academicYear->name,
            ] : null,
            'awarded_at' => $this->awarded_at?->toDateString(),
            'status' => $this->status,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
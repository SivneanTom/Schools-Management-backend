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
            'student' => [
                'id' => $this->student?->id,
                'student_code' => $this->student?->student_code,

                'full_name_km' =>
                trim(
                    ($this->student?->first_name_km ?? '') .
                        ' ' .
                        ($this->student?->last_name_km ?? '')
                ),

                'full_name_en' =>
                trim(
                    ($this->student?->first_name_en ?? '') .
                        ' ' .
                        ($this->student?->last_name_en ?? '')
                ),
            ],
            'scholarship' => [
                'id' => $this->scholarship?->id,
                'name_km' => $this->scholarship?->name_km,
                'name_en' => $this->scholarship?->name_en,
                'discount_type' => $this->scholarship?->discount_type,
                'discount_value' => $this->scholarship?->discount_value,
            ],
            'academic_year' => [
                'id' => $this->academicYear?->id,
                'name' => $this->academicYear?->name,
            ],
            'awarded_at' => optional($this->awarded_at)->format('Y-m-d'),
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

<?php

namespace App\Http\Resources\Teacher;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeacherListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'teacherCode' => $this->teacher_code,

            'firstNameKm' => $this->first_name_km,
            'lastNameKm' => $this->last_name_km,

            'firstNameEn' => $this->first_name_en,
            'lastNameEn' => $this->last_name_en,

            'gender' => $this->gender,

            'phone' => $this->phone,

            'specialization' => $this->specialization,

            'status' => $this->status,
        ];
    }
}
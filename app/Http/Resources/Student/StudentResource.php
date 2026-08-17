<?php

namespace App\Http\Resources\Student;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


// This controls how Student data is returned as JSON.

class StudentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'studentCode' => $this->student_code,

            'firstNameKm' => $this->first_name_km,
            'lastNameKm' => $this->last_name_km,

            'firstNameEn' => $this->first_name_en,
            'lastNameEn' => $this->last_name_en,

            'gender' => $this->gender,

            'dateOfBirth' =>
                $this->date_of_birth?->format('Y-m-d'),

            'phone' => $this->phone,

            'addressKm' => $this->address_km,
            'addressEn' => $this->address_en,

            'admissionDate' =>
                $this->admission_date?->format('Y-m-d'),

            'status' => $this->status,

            'user' => [
                'id' => $this->user?->id,
                'username' => $this->user?->username,
                'email' => $this->user?->email,
                'preferredLanguage' =>
                    $this->user?->preferred_language,
                'status' =>
                    $this->user?->status,

                'role' => [
                    'id' =>
                        $this->user?->role?->id,

                    'code' =>
                        $this->user?->role?->code,

                    'nameKm' =>
                        $this->user?->role?->name_km,

                    'nameEn' =>
                        $this->user?->role?->name_en,
                ],
            ],

            'createdAt' =>
                $this->created_at?->toISOString(),

            'updatedAt' =>
                $this->updated_at?->toISOString(),
        ];
    }
}
<?php

namespace App\Http\Resources\Parent;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ParentResource extends JsonResource
{
    // This is the full Parent response formatter.
    // You use it when you want detailed information.
    
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'parentCode' => $this->parent_code,

            'firstNameKm' => $this->first_name_km,
            'lastNameKm' => $this->last_name_km,

            'firstNameEn' => $this->first_name_en,
            'lastNameEn' => $this->last_name_en,

            'gender' => $this->gender,

            'phone' => $this->phone,

            'addressKm' => $this->address_km,
            'addressEn' => $this->address_en,

            'status' => $this->status,

            'user' => [
                'id' => $this->user?->id,
                'username' => $this->user?->username,
                'email' => $this->user?->email,
                'preferredLanguage' =>
                    $this->user?->preferred_language,
                'status' => $this->user?->status,

                'role' => [
                    'id' => $this->user?->role?->id,
                    'code' => $this->user?->role?->code,
                    'nameKm' => $this->user?->role?->name_km,
                    'nameEn' => $this->user?->role?->name_en,
                ],
            ],

            'students' => $this->whenLoaded(
                'students',
                function () {
                    return $this->students->map(
                        function ($student) {
                            return [
                                'id' => $student->id,
                                'studentCode' =>
                                    $student->student_code,

                                'firstNameKm' =>
                                    $student->first_name_km,

                                'lastNameKm' =>
                                    $student->last_name_km,

                                'firstNameEn' =>
                                    $student->first_name_en,

                                'lastNameEn' =>
                                    $student->last_name_en,

                                'relationship' =>
                                    $student->pivot?->relationship,

                                'isPrimary' =>
                                    (bool) $student->pivot?->is_primary,
                            ];
                        }
                    );
                }
            ),

            'createdAt' =>
                $this->created_at?->toISOString(),

            'updatedAt' =>
                $this->updated_at?->toISOString(),
        ];
    }
}
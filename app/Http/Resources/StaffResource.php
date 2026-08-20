<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StaffResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'staffNo' => $this->staff_no,
            'fullNameKm' => $this->full_name_km,
            'fullNameEn' => $this->full_name_en,
            'phone' => $this->phone,
            'hireDate' => $this->hire_date?->format('Y-m-d'),
            'positionTitleKm' => $this->position_title_km,
            'positionTitleEn' => $this->position_title_en,
            'status' => $this->status,

            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'username' => $this->user->username,
                    'email' => $this->user->email,
                    'preferredLanguage' => $this->user->preferred_language,
                    'status' => $this->user->status,
                    'role' => $this->user->relationLoaded('role') && $this->user->role ? [
                        'id' => $this->user->role->id,
                        'code' => $this->user->role->code,
                        'nameKm' => $this->user->role->name_km,
                        'nameEn' => $this->user->role->name_en,
                    ] : null,
                ];
            }),

            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }
}

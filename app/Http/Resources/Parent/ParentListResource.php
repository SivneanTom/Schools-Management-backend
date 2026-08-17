<?php

namespace App\Http\Resources\Parent;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ParentListResource extends JsonResource
{
    // You created this because the full ParentResource was too large for:GET /api/parents

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
            'status' => $this->status,
        ];
    }
}
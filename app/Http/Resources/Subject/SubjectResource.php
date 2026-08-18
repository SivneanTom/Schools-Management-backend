<?php

namespace App\Http\Resources\Subject;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'nameKm' => $this->name_km,
            'nameEn' => $this->name_en,
            'descriptionKm' => $this->description_km,
            'descriptionEn' => $this->description_en,
            'creditHours' => $this->credit_hours,
            'isActive' => $this->is_active,
            'createdAt' => $this->created_at?->toISOString(),
            'updatedAt' => $this->updated_at?->toISOString(),
        ];
    }
}

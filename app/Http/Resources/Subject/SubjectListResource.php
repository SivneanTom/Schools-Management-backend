<?php

namespace App\Http\Resources\Subject;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubjectListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'nameKm' => $this->name_km,
            'nameEn' => $this->name_en,
            'creditHours' => $this->credit_hours,
            'isActive' => $this->is_active,
        ];
    }
}

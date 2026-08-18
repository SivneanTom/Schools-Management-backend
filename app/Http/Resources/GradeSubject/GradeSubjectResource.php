<?php

namespace App\Http\Resources\GradeSubject;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GradeSubjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'grade' => [
                'id' => $this->grade?->id,
                'code' => $this->grade?->code,
                'nameKm' => $this->grade?->name_km,
                'nameEn' => $this->grade?->name_en,
                'status' => $this->grade?->status,
            ],

            'subject' => [
                'id' => $this->subject?->id,
                'code' => $this->subject?->code,
                'nameKm' => $this->subject?->name_km,
                'nameEn' => $this->subject?->name_en,
                'descriptionKm' => $this->subject?->description_km,
                'descriptionEn' => $this->subject?->description_en,
                'isActive' => $this->subject?->is_active,
            ],

            'creditHours' => $this->credit_hours,
            'isRequired' => $this->is_required,

            'createdAt' => $this->created_at?->toISOString(),
            'updatedAt' => $this->updated_at?->toISOString(),
        ];
    }
}

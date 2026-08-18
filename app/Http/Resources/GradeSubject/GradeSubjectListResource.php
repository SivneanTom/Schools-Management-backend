<?php

namespace App\Http\Resources\GradeSubject;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GradeSubjectListResource extends JsonResource
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
            ],

            'subject' => [
                'id' => $this->subject?->id,
                'code' => $this->subject?->code,
                'nameKm' => $this->subject?->name_km,
                'nameEn' => $this->subject?->name_en,
                'isActive' => $this->subject?->is_active,
            ],

            'creditHours' => $this->credit_hours,
            'isRequired' => $this->is_required,
        ];
    }
}

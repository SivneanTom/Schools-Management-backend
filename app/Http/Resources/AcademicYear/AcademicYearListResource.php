<?php

namespace App\Http\Resources\AcademicYear;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AcademicYearListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,

            'startDate' =>
                $this->start_date?->format('Y-m-d'),

            'endDate' =>
                $this->end_date?->format('Y-m-d'),

            'status' => $this->status,
        ];
    }
}
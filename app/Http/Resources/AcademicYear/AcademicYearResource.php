<?php

namespace App\Http\Resources\AcademicYear;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AcademicYearResource extends JsonResource
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

            'createdAt' =>
                $this->created_at?->toISOString(),

            'updatedAt' =>
                $this->updated_at?->toISOString(),
        ];
    }
}
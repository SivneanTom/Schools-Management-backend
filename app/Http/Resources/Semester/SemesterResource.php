<?php

namespace App\Http\Resources\Semester;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SemesterResource extends JsonResource
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

            'academicYear' => [
                'id' => $this->academicYear?->id,
                'name' => $this->academicYear?->name,
                'startDate' =>
                    $this->academicYear?->start_date
                        ?->format('Y-m-d'),
                'endDate' =>
                    $this->academicYear?->end_date
                        ?->format('Y-m-d'),
                'status' =>
                    $this->academicYear?->status,
            ],

            'createdAt' =>
                $this->created_at?->toISOString(),

            'updatedAt' =>
                $this->updated_at?->toISOString(),
        ];
    }
}
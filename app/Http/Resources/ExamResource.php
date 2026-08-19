<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExamResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'semester_id' => $this->semester_id,
            'semester' => $this->whenLoaded(
                'semester',
                fn() => [
                    'id' => $this->semester->id,
                    'name' => $this->semester->name,

                    'start_date' =>
                    $this->semester->start_date?->format('Y-m-d'),

                    'end_date' =>
                    $this->semester->end_date?->format('Y-m-d'),

                    'status' =>
                    $this->semester->status,
                ]
            ),
            'name_km' => $this->name_km,
            'name_en' => $this->name_en,
            'exam_type' => $this->exam_type,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

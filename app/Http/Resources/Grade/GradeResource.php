<?php

namespace App\Http\Resources\Grade;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GradeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'code' => $this->code,

            'nameKm' => $this->name_km,
            'nameEn' => $this->name_en,

            'orderNo' => $this->order_no,

            'status' => $this->status,

            'createdAt' =>
                $this->created_at?->toISOString(),

            'updatedAt' =>
                $this->updated_at?->toISOString(),
        ];
    }
}
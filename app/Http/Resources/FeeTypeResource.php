<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeeTypeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name_km' => $this->name_km,
            'name_en' => $this->name_en,
            'description_km' => $this->description_km,
            'description_en' => $this->description_en,
            'default_amount' => $this->default_amount,
            'frequency' => $this->frequency,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentMethodResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->id,

            'code' => $this->code,

            'name_km' => $this->name_km,

            'name_en' => $this->name_en,

            'is_active' => $this->is_active,


            'created_at' => $this->created_at,

            'updated_at' => $this->updated_at,
        ];
    }
}
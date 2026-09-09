<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FinanceStudentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_code' =>
                $this->student_code,

            'full_name_km' => trim(
                $this->first_name_km .
                ' ' .
                $this->last_name_km
            ),

            'full_name_en' => trim(
                ($this->first_name_en ?? '') .
                ' ' .
                ($this->last_name_en ?? '')
            ),

            'status' => $this->status,
        ];
    }
}
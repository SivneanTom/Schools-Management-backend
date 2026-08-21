<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentFeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student' => [
                'id' => $this->student?->id,
                'student_code' => $this->student?->student_code,

                'full_name_km' =>
                trim(
                    ($this->student?->first_name_km ?? '')
                        . ' ' .
                        ($this->student?->last_name_km ?? '')
                ),

                'full_name_en' =>
                trim(
                    ($this->student?->first_name_en ?? '')
                        . ' ' .
                        ($this->student?->last_name_en ?? '')
                ),
            ],
            'fee_type' => [
                'id' => $this->feeType?->id,
                'code' => $this->feeType?->code,
                'name_km' => $this->feeType?->name_km,
                'name_en' => $this->feeType?->name_en,
                'frequency' => $this->feeType?->frequency,
            ],
            'academic_year' => [
                'id' => $this->academicYear?->id,
                'name' => $this->academicYear?->name,
            ],
            'amount' => $this->amount,
            'due_date' => optional($this->due_date)->format('Y-m-d'),
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

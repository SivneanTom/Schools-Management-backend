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
            'student' => $this->student ? [
                'id' => $this->student->id,
                'student_code' => $this->student->student_code,
                'full_name_km' => trim(
                    ($this->student->first_name_km ?? '') . ' ' .
                    ($this->student->last_name_km ?? '')
                ),
                'full_name_en' => trim(
                    ($this->student->first_name_en ?? '') . ' ' .
                    ($this->student->last_name_en ?? '')
                ),
            ] : null,
            'fee_type' => $this->feeType ? [
                'id' => $this->feeType->id,
                'code' => $this->feeType->code,
                'name_km' => $this->feeType->name_km,
                'name_en' => $this->feeType->name_en,
                'frequency' => $this->feeType->frequency,
            ] : null,
            'academic_year' => $this->academicYear ? [
                'id' => $this->academicYear->id,
                'name' => $this->academicYear->name,
            ] : null,
            'amount' => $this->amount,
            'due_date' => $this->due_date?->format('Y-m-d'),
            'status' => $this->status,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
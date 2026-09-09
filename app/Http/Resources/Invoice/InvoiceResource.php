<?php

namespace App\Http\Resources\Invoice;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_no' => $this->invoice_no,
            'student' => $this->whenLoaded('student', function () {
                return [
                    'id' => $this->student->id,
                    'student_code' => $this->student->student_code,
                    'full_name_km' => trim(
                        $this->student->first_name_km.' '.
                        $this->student->last_name_km
                    ),
                    'full_name_en' => trim(
                        $this->student->first_name_en.' '.
                        $this->student->last_name_en
                    ),
                ];
            }),
            'academic_year' => $this->whenLoaded(
                'academicYear',
                function () {
                    return [
                        'id' => $this->academicYear->id,
                        'name' => $this->academicYear->name,
                    ];
                }
            ),
            'issued_date' => $this->issued_date,
            'due_date' => $this->due_date,
            'subtotal' => $this->subtotal,
            'discount_total' => $this->discount_total,
            'total_amount' => $this->total_amount,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
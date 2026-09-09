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

            'student' => $this->whenLoaded(
                'student',
                fn() => [
                    'id' => $this->student?->id,
                    'student_code' =>
                    $this->student?->student_code,
                    'full_name_km' => trim(
                        ($this->student?->first_name_km ?? '') .
                            ' ' .
                            ($this->student?->last_name_km ?? '')
                    ),
                    'full_name_en' => trim(
                        ($this->student?->first_name_en ?? '') .
                            ' ' .
                            ($this->student?->last_name_en ?? '')
                    ),
                ]
            ),

            'academic_year' => $this->whenLoaded(
                'academicYear',
                fn() => [
                    'id' => $this->academicYear?->id,
                    'name' => $this->academicYear?->name,
                ]
            ),

            'issued_date' => optional(
                $this->issued_date
            )?->format('Y-m-d'),

            'due_date' => optional(
                $this->due_date
            )?->format('Y-m-d'),

            'subtotal' => number_format(
                (float) $this->subtotal,
                2,
                '.',
                ''
            ),

            'discount_total' => number_format(
                (float) $this->discount_total,
                2,
                '.',
                ''
            ),

            'total_amount' => number_format(
                (float) $this->total_amount,
                2,
                '.',
                ''
            ),

            'status' => $this->status,

            'created_at' =>
            $this->created_at?->toISOString(),

            'updated_at' =>
            $this->updated_at?->toISOString(),
        ];
    }
}

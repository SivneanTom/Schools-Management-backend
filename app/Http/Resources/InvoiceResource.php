<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->id,

            'invoice_no' => $this->invoice_no,


            'student' => [

                'id' => $this->student?->id,

                'student_code' => 
                    $this->student?->student_code,

                'name_km' =>
                    trim(
                        ($this->student?->first_name_km ?? '')
                        . ' ' .
                        ($this->student?->last_name_km ?? '')
                    ),

                'name_en' =>
                    trim(
                        ($this->student?->first_name_en ?? '')
                        . ' ' .
                        ($this->student?->last_name_en ?? '')
                    ),
            ],


            'academic_year' => [

                'id' =>
                    $this->academicYear?->id,

                'name' =>
                    $this->academicYear?->name,
            ],


            'issued_date' =>
                optional($this->issued_date)
                    ->format('Y-m-d'),


            'due_date' =>
                optional($this->due_date)
                    ->format('Y-m-d'),


            'total_amount' =>
                $this->total_amount,


            'status' =>
                $this->status ?? 'UNPAID',
        ];
    }
}
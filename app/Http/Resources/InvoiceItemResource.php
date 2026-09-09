<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_id' => $this->invoice_id,
            'student_fee_id' => $this->student_fee_id,
            'description_km' => $this->description_km,
            'description_en' => $this->description_en,

            'quantity' => number_format(
                (float) $this->quantity,
                2,
                '.',
                ''
            ),

            'unit_amount' => number_format(
                (float) $this->unit_amount,
                2,
                '.',
                ''
            ),

            'discount_amount' => number_format(
                (float) ($this->discount_amount ?? 0),
                2,
                '.',
                ''
            ),

            'line_total' => number_format(
                (float) $this->line_total,
                2,
                '.',
                ''
            )
        ];
    }
}
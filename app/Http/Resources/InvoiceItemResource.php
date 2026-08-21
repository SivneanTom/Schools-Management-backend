<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceItemResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'=>$this->id,
            'invoice_id'=>$this->invoice_id,
            'student_fee_id'=>$this->student_fee_id,
            'description_km'=>$this->description_km,
            'description_en'=>$this->description_en,
            'quantity'=>$this->quantity,
            'unit_amount'=>$this->unit_amount,
            'discount_amount'=>$this->discount_amount,
            'line_total'=>$this->line_total
        ];
    }
}

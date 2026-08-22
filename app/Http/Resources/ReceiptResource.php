<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReceiptResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'=>$this->id,

            'receipt_no'=>$this->receipt_no,

            'payment'=>[
                'id'=>$this->payment?->id,
                'payment_no'=>$this->payment?->payment_no,
                'amount'=>$this->payment?->amount,
            ],

            'issued_at'=>$this->issued_at?->format('Y-m-d'),

            'file_url'=>$this->file_url,

            'created_at'=>$this->created_at,
            'updated_at'=>$this->updated_at,
        ];
    }
}
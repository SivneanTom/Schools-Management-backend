<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [

            'id' => $this->id,


            'invoice' => [

                'id' => $this->invoice?->id,

                'invoice_no' => $this->invoice?->invoice_no,

                'total_amount' => $this->invoice?->total_amount,

                'status' => $this->invoice?->status,

            ],



            'payment_method' => [

                'id' => $this->paymentMethod?->id,

                'code' => $this->paymentMethod?->code,

                'name_km' => $this->paymentMethod?->name_km,

                'name_en' => $this->paymentMethod?->name_en,

            ],



            'received_by' => [

                'id' => $this->receivedBy?->id,

                'name_km' => $this->receivedBy?->full_name_km,

                'name_en' => $this->receivedBy?->full_name_en,

            ],



            'payment_no' => $this->payment_no,


            'amount' => $this->amount,


            'paid_at' => $this->paid_at
                ? $this->paid_at->format('Y-m-d H:i:s')
                : null,


            'reference_no' => $this->reference_no,


            'status' => $this->status,


            'created_at' => $this->created_at,

            'updated_at' => $this->updated_at,

        ];
    }
}
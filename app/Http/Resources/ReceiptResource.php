<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReceiptResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'receipt_no' => $this->receipt_no,
            'issued_at' => $this->issued_at?->toISOString(),
            'file_url' => $this->file_url,

            'payment' => [
                'id' => $this->payment?->id,
                'payment_no' =>
                    $this->payment?->payment_no,
                'amount' => number_format(
                    (float) (
                        $this->payment?->amount ?? 0
                    ),
                    2,
                    '.',
                    ''
                ),
                'paid_at' =>
                    $this->payment?->paid_at?->toISOString(),
                'reference_no' =>
                    $this->payment?->reference_no,
                'status' =>
                    $this->payment?->status,

                'payment_method' => [
                    'id' =>
                        $this->payment
                            ?->paymentMethod?->id,
                    'code' =>
                        $this->payment
                            ?->paymentMethod?->code,
                    'name_en' =>
                        $this->payment
                            ?->paymentMethod?->name_en
                ]
            ]
        ];
    }
}
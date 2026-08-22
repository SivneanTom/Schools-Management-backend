<?php

namespace App\Http\Requests\Payment;
use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {

        return [

            'invoice_id' => [
                'required',
                'exists:invoices,id'
            ],

            'payment_method_id' => [
                'required',
                'exists:payment_methods,id'
            ],

            'received_by_staff_id' => [
                'nullable',
                'exists:staff,id'
            ],

            'amount' => [
                'required',
                'numeric',
                'min:0'
            ],

            'paid_at' => [
                'nullable',
                'date'
            ],

            'reference_no' => [
                'nullable',
                'string'
            ],

            'status' => [
                'nullable',
                'in:PENDING,COMPLETED,CANCELLED'
            ]

        ];
    }
}

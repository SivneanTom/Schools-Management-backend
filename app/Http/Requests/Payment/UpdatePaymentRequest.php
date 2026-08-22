<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class UpdatePaymentRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }



    public function rules(): array
    {

        return [

            'payment_method_id'=>[
                'sometimes',
                'exists:payment_methods,id'
            ],


            'amount'=>[
                'sometimes',
                'numeric',
                'min:0.01'
            ],


            'paid_at'=>[
                'nullable',
                'date'
            ],


            'reference_no'=>[
                'nullable',
                'string'
            ],


            'status'=>[
                'sometimes',
                Rule::in([
                    'PENDING',
                    'COMPLETED',
                    'CANCELLED'
                ])
            ]

        ];

    }

}
<?php

namespace App\Http\Requests\PaymentMethod;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class UpdatePaymentMethodRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }



    public function rules(): array
    {

        $id=$this->route('payment_method')?->id;


        return [

            'code'=>[
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique(
                    'payment_methods',
                    'code'
                )->ignore($id)
            ],


            'name_km'=>[
                'sometimes',
                'required',
                'string'
            ],


            'name_en'=>[
                'nullable',
                'string'
            ],


            'is_active'=>[
                'sometimes',
                'boolean'
            ]

        ];
    }
}
<?php

namespace App\Http\Requests\PaymentMethod;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentMethodRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }



    public function rules(): array
    {
        return [

            'code' => [
                'required',
                'string',
                'max:50',
                'unique:payment_methods,code'
            ],


            'name_km'=>[
                'required',
                'string',
                'max:255'
            ],


            'name_en'=>[
                'nullable',
                'string',
                'max:255'
            ],


            'is_active'=>[
                'sometimes',
                'boolean'
            ]

        ];
    }



    protected function prepareForValidation()
    {

        if($this->code){

            $this->merge([

                'code'=>strtoupper(
                    trim($this->code)
                )

            ]);

        }

    }

}
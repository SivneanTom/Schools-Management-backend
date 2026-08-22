<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [

            'payment_id' => [
                'required',
                'exists:payments,id',
                'unique:receipts,payment_id'
            ],

            'receipt_no' => [
                'required',
                'unique:receipts,receipt_no'
            ],

            'issued_at' => [
                'required',
                'date'
            ],

            'file_url' => [
                'nullable',
                'string'
            ],

        ];
    }
}

<?php

namespace App\Http\Requests\InvoiceItem;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'description_km'=>'nullable|string|max:255',
            'description_en'=>'nullable|string|max:255',
            'quantity'=>'nullable|numeric|min:0.01',
            'unit_amount'=>'nullable|numeric|min:0',
            'discount_amount'=>'nullable|numeric|min:0'
        ];
    }
}

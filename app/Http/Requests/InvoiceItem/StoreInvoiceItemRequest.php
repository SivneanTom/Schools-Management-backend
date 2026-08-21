<?php

namespace App\Http\Requests\InvoiceItem;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'invoice_id'=>'required|exists:invoices,id',
            'student_fee_id'=>'nullable|exists:student_fees,id',
            'description_km'=>'nullable|string|max:255',
            'description_en'=>'nullable|string|max:255',
            'quantity'=>'required|numeric|min:0.01',
            'unit_amount'=>'required|numeric|min:0',
            'discount_amount'=>'nullable|numeric|min:0'
        ];
    }
}

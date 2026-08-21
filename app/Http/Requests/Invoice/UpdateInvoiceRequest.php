<?php

namespace App\Http\Requests\Invoice;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'due_date'=>'nullable|date',
            'discount_total'=>'nullable|numeric|min:0',
            'status'=>'nullable|in:UNPAID,PARTIAL,PAID,OVERDUE,CANCELLED'
        ];
    }
}

<?php

namespace App\Http\Requests\Invoice;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'student_id'=>'required|exists:students,id',
            'academic_year_id'=>'required|exists:academic_years,id',
            'issued_date'=>'required|date',
            'due_date'=>'nullable|date|after_or_equal:issued_date',
            'subtotal'=>'required|numeric|min:0',
            'discount_total'=>'nullable|numeric|min:0',
            'status'=>'nullable|in:UNPAID,PARTIAL,PAID,OVERDUE,CANCELLED'
        ];
    }
}

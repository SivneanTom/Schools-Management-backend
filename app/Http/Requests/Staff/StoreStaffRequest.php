<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id', 'unique:staff,user_id'],
            'staff_no' => ['required', 'string', 'max:50', 'unique:staff,staff_no'],
            'full_name_km' => ['required', 'string', 'max:255'],
            'full_name_en' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'hire_date' => ['nullable', 'date'],
            'position_title_km' => ['nullable', 'string', 'max:255'],
            'position_title_en' => ['nullable', 'string', 'max:255'],
            'status' => ['sometimes', 'string', Rule::in(['ACTIVE', 'INACTIVE'])],
        ];
    }
}

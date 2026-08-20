<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $staffId = $this->route('staff')?->id ?? $this->route('staff');

        return [
            'user_id' => [
                'sometimes', 'required', 'integer', 'exists:users,id',
                Rule::unique('staff', 'user_id')->ignore($staffId),
            ],
            'staff_no' => [
                'sometimes', 'required', 'string', 'max:50',
                Rule::unique('staff', 'staff_no')->ignore($staffId),
            ],
            'full_name_km' => ['sometimes', 'required', 'string', 'max:255'],
            'full_name_en' => ['sometimes', 'nullable', 'string', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:30'],
            'hire_date' => ['sometimes', 'nullable', 'date'],
            'position_title_km' => ['sometimes', 'nullable', 'string', 'max:255'],
            'position_title_en' => ['sometimes', 'nullable', 'string', 'max:255'],
            'status' => ['sometimes', 'required', 'string', Rule::in(['ACTIVE', 'INACTIVE'])],
        ];
    }
}

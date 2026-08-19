<?php

namespace App\Http\Requests\Room;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name_km' => ['sometimes', 'required', 'string', 'max:150'],
            'name_en' => ['sometimes', 'nullable', 'string', 'max:150'],
            'building_km' => ['sometimes', 'nullable', 'string', 'max:150'],
            'building_en' => ['sometimes', 'nullable', 'string', 'max:150'],
            'capacity' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'status' => ['sometimes', 'string', Rule::in(['ACTIVE', 'INACTIVE'])],
        ];
    }
}

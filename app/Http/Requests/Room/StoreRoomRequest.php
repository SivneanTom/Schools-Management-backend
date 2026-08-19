<?php

namespace App\Http\Requests\Room;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name_km' => ['required', 'string', 'max:150'],
            'name_en' => ['nullable', 'string', 'max:150'],
            'building_km' => ['nullable', 'string', 'max:150'],
            'building_en' => ['nullable', 'string', 'max:150'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'status' => ['sometimes', 'string', Rule::in(['ACTIVE', 'INACTIVE'])],
        ];
    }
}

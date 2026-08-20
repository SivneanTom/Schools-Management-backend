<?php

namespace App\Http\Requests\LearningMaterial;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLearningMaterialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'teacher_assignment_id' => ['required', 'integer', 'exists:teacher_assignments,id'],
            'title_km' => ['required', 'string', 'max:255'],
            'title_en' => ['nullable', 'string', 'max:255'],
            'description_km' => ['nullable', 'string'],
            'description_en' => ['nullable', 'string'],
            'material_type' => ['required', 'string', Rule::in(['DOCUMENT', 'PDF', 'VIDEO', 'LINK', 'IMAGE', 'OTHER'])],
            'file_url' => ['nullable', 'string', 'max:2000'],
            'published_at' => ['nullable', 'date'],
        ];
    }
}

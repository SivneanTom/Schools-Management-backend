<?php

namespace App\Http\Requests\LearningMaterial;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLearningMaterialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'teacher_assignment_id' => ['sometimes', 'required', 'integer', 'exists:teacher_assignments,id'],
            'title_km' => ['sometimes', 'required', 'string', 'max:255'],
            'title_en' => ['sometimes', 'nullable', 'string', 'max:255'],
            'description_km' => ['sometimes', 'nullable', 'string'],
            'description_en' => ['sometimes', 'nullable', 'string'],
            'material_type' => ['sometimes', 'required', 'string', Rule::in(['DOCUMENT', 'PDF', 'VIDEO', 'LINK', 'IMAGE', 'OTHER'])],
            'file_url' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'published_at' => ['sometimes', 'nullable', 'date'],
        ];
    }
}

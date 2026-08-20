<?php

namespace App\Http\Requests\Assignment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAssignmentRequest extends FormRequest
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
            'assigned_at' => ['sometimes', 'required', 'date'],
            'due_at' => ['sometimes', 'required', 'date'],
            'max_score' => ['sometimes', 'required', 'numeric', 'gt:0'],
            'status' => ['sometimes', 'required', 'string', Rule::in(['DRAFT', 'PUBLISHED', 'CLOSED', 'CANCELLED'])],
        ];
    }
}

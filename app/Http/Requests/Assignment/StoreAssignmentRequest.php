<?php

namespace App\Http\Requests\Assignment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAssignmentRequest extends FormRequest
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
            'assigned_at' => ['required', 'date'],
            'due_at' => ['required', 'date', 'after:assigned_at'],
            'max_score' => ['required', 'numeric', 'gt:0'],
            'status' => ['sometimes', 'string', Rule::in(['DRAFT', 'PUBLISHED', 'CLOSED', 'CANCELLED'])],
        ];
    }
}

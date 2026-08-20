<?php

namespace App\Http\Requests\AssignmentSubmission;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAssignmentSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'assignment_id' => ['required', 'integer', 'exists:assignments,id'],
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'submitted_at' => ['nullable', 'date'],
            'content' => ['nullable', 'string'],
            'file_url' => ['nullable', 'string', 'max:2000'],
            'score' => ['nullable', 'numeric', 'min:0'],
            'feedback_km' => ['nullable', 'string'],
            'feedback_en' => ['nullable', 'string'],
            'status' => ['sometimes', 'string', Rule::in(['SUBMITTED', 'LATE', 'GRADED', 'RETURNED'])],
        ];
    }
}

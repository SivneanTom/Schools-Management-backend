<?php

namespace App\Http\Requests\AssignmentSubmission;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAssignmentSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'assignment_id' => ['sometimes', 'required', 'integer', 'exists:assignments,id'],
            'student_id' => ['sometimes', 'required', 'integer', 'exists:students,id'],
            'submitted_at' => ['sometimes', 'nullable', 'date'],
            'content' => ['sometimes', 'nullable', 'string'],
            'file_url' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'score' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'feedback_km' => ['sometimes', 'nullable', 'string'],
            'feedback_en' => ['sometimes', 'nullable', 'string'],
            'status' => ['sometimes', 'required', 'string', Rule::in(['SUBMITTED', 'LATE', 'GRADED', 'RETURNED'])],
        ];
    }
}

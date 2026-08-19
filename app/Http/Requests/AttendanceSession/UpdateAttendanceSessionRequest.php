<?php

namespace App\Http\Requests\AttendanceSession;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAttendanceSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'teacher_assignment_id' => [
                'sometimes',
                'required',
                'integer',
                'exists:teacher_assignments,id',
            ],

            'attendance_date' => [
                'sometimes',
                'required',
                'date_format:Y-m-d',
            ],

            'start_time' => [
                'sometimes',
                'required',
                'date_format:H:i',
            ],

            'end_time' => [
                'sometimes',
                'required',
                'date_format:H:i',
            ],

            'status' => [
                'sometimes',
                'required',
                'string',
                Rule::in([
                    'OPEN',
                    'CLOSED',
                    'CANCELLED',
                ]),
            ],
        ];
    }
}

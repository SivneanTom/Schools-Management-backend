<?php

namespace App\Http\Requests\AttendanceSession;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAttendanceSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'teacher_assignment_id' => [
                'required',
                'integer',
                'exists:teacher_assignments,id',
            ],

            'attendance_date' => [
                'required',
                'date_format:Y-m-d',
            ],

            'start_time' => [
                'required',
                'date_format:H:i',
            ],

            'end_time' => [
                'required',
                'date_format:H:i',
                'after:start_time',
            ],

            'status' => [
                'sometimes',
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

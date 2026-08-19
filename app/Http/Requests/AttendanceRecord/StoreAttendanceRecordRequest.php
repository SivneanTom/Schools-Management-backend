<?php

namespace App\Http\Requests\AttendanceRecord;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAttendanceRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'attendance_session_id' => [
                'required',
                'integer',
                'exists:attendance_sessions,id',
            ],

            'student_id' => [
                'required',
                'integer',
                'exists:students,id',
            ],

            'status' => [
                'required',
                'string',
                Rule::in([
                    'PRESENT',
                    'ABSENT',
                    'LATE',
                    'EXCUSED',
                ]),
            ],

            'remarks_km' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'remarks_en' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }
}

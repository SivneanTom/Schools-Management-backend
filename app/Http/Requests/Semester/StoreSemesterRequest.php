<?php

namespace App\Http\Requests\Semester;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSemesterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'academicYearId' => [
                'required',
                'integer',
                'exists:academic_years,id',
            ],

            'name' => [
                'required',
                'string',
                'max:100',

                Rule::unique('semesters', 'name')
                    ->where(
                        fn ($query) =>
                            $query->where(
                                'academic_year_id',
                                $this->input('academicYearId')
                            )
                    ),
            ],

            'startDate' => [
                'required',
                'date',
            ],

            'endDate' => [
                'required',
                'date',
                'after:startDate',
            ],

            'status' => [
                'nullable',
                Rule::in([
                    'ACTIVE',
                    'INACTIVE',
                ]),
            ],
        ];
    }
}
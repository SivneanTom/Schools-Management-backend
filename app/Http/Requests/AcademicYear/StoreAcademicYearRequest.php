<?php

namespace App\Http\Requests\AcademicYear;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAcademicYearRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                'unique:academic_years,name',
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
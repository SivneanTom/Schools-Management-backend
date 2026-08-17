<?php

namespace App\Http\Requests\Student;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
         $studentID = $this->route('student')?->id;

        return [
           'username' =>[
                'sometimes' ,
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'username')
                    ->ignore($this->route('student')?->user_id),
            ],

           'email' =>[
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'email')
                    ->ignore($this->route('student')?->user_id),
           ],

           'preferredLanguage' => [
                'sometimes',
                Rule::in(['KM', 'EN']),
           ],

           'studentCode' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('users', 'student_code')
                    ->ignore($studentID),
           ],

           'firstNameKm' => [
                'sometimes',
                'required',
                'string',
                'max:255',
           ],

           'lastNameKm' => [
                'sometimes',
                'required',
                'string',
                'max:255',
           ],

           'firstNameEn' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
           ],

           'lastNameEn' => [
                'somestiimes',
                'nullable',
                'string',
                'max:255',
           ],

           'gender' => [
                'sometime',
                Rule::in(['MALE' , 'FEMALE'])
           ],

           'dateOfBirth' => [
                'sometimes',
                'nullable',
                'string',
                'max:30',
           ],

           'phone' => [
                'sometime',
                'nullable',
                'string',
           ],

           'addressKm' => [
                'sometimes' ,
                'nullable',
                'string',
           ],

           'addressEn' => [
                'sometimes',
                'nullable',
                'string',
           ],

           'admissionDate' =>[
                'sometimes',
                'nullable',
                'date',
           ],
                
        ];
    }
}

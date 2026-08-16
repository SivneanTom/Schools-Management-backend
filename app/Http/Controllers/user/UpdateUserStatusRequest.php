<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

//  Staus
class UpdateUserStatusRequest extends Controller
{
    public function authorize(): bool {
        return true;
    }

    public function rules(): array {
        return [
            'status' => [
                Rule::in([
                    'ACTIVE',
                    'INACTIVE',
                    'SUSPENDED',
                ]),
            ],
        ];
    }
}

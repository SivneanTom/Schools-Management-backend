<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

    //  Login 

    public function login(LoginRequest $request): JsonResponse

    {
        $result = $this->authService->login(
            $request->string('email')->toString(),
            $request->string('password')->toString()
        );

        return response()->json([
            'success' => true,
            'message' => 'Login Successfully.',
            'data' => [
                'accessToken' => $result['token'],
                'user' => $result['user'],
            ],
        ]);
    }

    // Me 

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('role');

        return response()->json([
            'success' => true,

            'data' => [
                'id' => $user->id,

                'username' => $user->username,

                'email' => $user->email,

                'preferredLanguage' =>
                $user->preferred_language,

                'status' =>
                $user->status,

                'role' => [
                    'code' =>
                    $user->role->code,

                    'nameKm' =>
                    $user->role->name_km,

                    'nameEn' =>
                    $user->role->name_en,
                ],
            ],
        ]);
    }
    // Logout

    public function logout(Request $request): JsonResponse
    {

        $request->user()
            ->currentAccessToken()
            ?->delete();

        return response()->json([
            'success' => true,
            'message' => 'Loged out Successfully.',
        ]);
    }
}

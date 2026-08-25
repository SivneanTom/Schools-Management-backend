<?php

use App\Http\Controllers\Api\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

Route::middleware([
    'auth:sanctum',
    'role:SUPER_ADMIN',
])->get('/test/super-admin', function () {
    return response()->json([
        'success' => true,
        'message' => 'You are allowed as SUPER_ADMIN.',
    ]);
});
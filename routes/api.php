<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Student\StudentController;
use GuzzleHttp\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\User\RoleController;
use App\Http\Controllers\Api\User\UserController;

//  Route for Auth
Route::prefix('auth')->group(function () {
    Route::post('/login', [
        AuthController::class,
        'login',
    ]);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [
            AuthController::class,
            'me',
        ]);

        Route::post('/logout', [
            AuthController::class,
            'logout',
        ]);
    });
});
// Test Super Admin
Route::middleware([
    'auth:sanctum',
    'role:SUPER_ADMIN'
])->get('/test/super-admin', function () {
    return response()->json([
        'success' => true,
        'message' => 'You are allow as SUPER_ADMIN.'
    ]);
});

//  // Role APIs
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/roles', [
        RoleController::class,
        'index',
    ]);

    Route::get('/roles/{role}', [
        RoleController::class,
        'show',
    ]);
});

// User

Route::middleware([
    'auth:sanctum',
    'role:SUPER_ADMIN',
])->group(function () {

    Route::get('/users', [
        UserController::class,
        'index'
    ]);

    Route::get('/users/{user}', [
        UserController::class,
        'show',
    ]);

    // Status Update
    Route::patch('/users/{user}/status', [
        UserController::class,
        'updateStatus',
    ]);

    //  Route For Student

    Route::middleware([
        'auth:sanctum',
        'role:SUPER_ADMIN, ADMIN',
    ])->group(function () {

        Route::post('/students', [
            StudentController::class,
            'store',
        ]);

        Route::get('/students', [
            StudentController::class,
            'index',
        ]);

        Route::get('/students/{student}', [
            StudentController::class,
            'show',
        ]);

        Route::patch('/students/{student}', [
            StudentController::class,
            'update',
        ]);

        Route::patch('/students/{student}/status', [
            StudentController::class,
            'updateStatus',
        ]);
    });
});
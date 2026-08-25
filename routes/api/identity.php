<?php

use App\Http\Controllers\Api\User\RoleController;
use App\Http\Controllers\Api\User\UserController;
use App\Http\Controllers\Api\Staff\StaffController;
use Illuminate\Support\Facades\Route;

// Roles: Super Admin + Admin can view
Route::middleware([
    'auth:sanctum',
    'role:SUPER_ADMIN,ADMIN',
])->group(function () {
    Route::get('/roles', [RoleController::class, 'index']);
    Route::get('/roles/{role}', [RoleController::class, 'show']);
});

// User management
Route::middleware([
    'auth:sanctum',
    'role:SUPER_ADMIN,ADMIN',
])->group(function () {
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::get('/users/{user}', [UserController::class, 'show']);
    Route::patch('/users/{user}/status', [UserController::class, 'updateStatus']);
});

// Staff management
Route::middleware([
    'auth:sanctum',
    'role:SUPER_ADMIN,ADMIN',
])->group(function () {
    Route::get('/staff', [StaffController::class, 'index']);
    Route::post('/staff', [StaffController::class, 'store']);
    Route::get('/staff/{staff}', [StaffController::class, 'show']);
    Route::put('/staff/{staff}', [StaffController::class, 'update']);
    Route::patch('/staff/{staff}', [StaffController::class, 'update']);
    Route::delete('/staff/{staff}', [StaffController::class, 'destroy']);
});
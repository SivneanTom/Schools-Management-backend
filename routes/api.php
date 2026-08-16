<?php

use App\Http\Controllers\Api\Auth\AuthController;
use GuzzleHttp\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\User\RoleController;
use App\Http\Controllers\Api\User\UserController;

//  Route for Auth
Route::prefix('auth')->group(function () {
    Route::post('/login' , [
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
        return response()->json ([
            'success' => true,
            'message' => 'You are allow as SUPER_ADMIN.'
        ]);
    });

    //  // Role APIs
    Route::middleware('auth:sanctum')->group(function () {

    Route::get('/roles', [
        RoleController::class,
        'index' ,
    ]);

    Route::get('/roles/{role}', [
        RoleController::class,
        'show',
    ]);
});

    // User

    Route::middleware([
        'auth:sanctum' , 
        'role:SUPER_ADMIN' ,
    ])->group(function () {
    
    Route::get('/users' , [
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
        
});
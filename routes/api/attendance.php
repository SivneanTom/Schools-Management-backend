
<?php

use App\Http\Controllers\Api\AttendanceSession\AttendanceSessionController;
use App\Http\Controllers\Api\AttendanceRecord\AttendanceRecordController;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth:sanctum',
    'role:SUPER_ADMIN,ADMIN,TEACHER',
])->group(function () {

    // Attendance Sessions
    Route::get('/attendance-sessions', [AttendanceSessionController::class, 'index']);
    Route::post('/attendance-sessions', [AttendanceSessionController::class, 'store']);
    Route::get('/attendance-sessions/{attendanceSession}', [AttendanceSessionController::class, 'show']);
    Route::put('/attendance-sessions/{attendanceSession}', [AttendanceSessionController::class, 'update']);
    Route::patch('/attendance-sessions/{attendanceSession}', [AttendanceSessionController::class, 'update']);

    // Attendance Records
    Route::get('/attendance-records', [AttendanceRecordController::class, 'index']);
    Route::post('/attendance-records', [AttendanceRecordController::class, 'store']);
    Route::get('/attendance-records/{attendanceRecord}', [AttendanceRecordController::class, 'show']);
    Route::put('/attendance-records/{attendanceRecord}', [AttendanceRecordController::class, 'update']);
    Route::patch('/attendance-records/{attendanceRecord}', [AttendanceRecordController::class, 'update']);
});

// Deletion is administrative only
Route::middleware([
    'auth:sanctum',
    'role:SUPER_ADMIN,ADMIN',
])->group(function () {
    Route::delete('/attendance-sessions/{attendanceSession}', [AttendanceSessionController::class, 'destroy']);
    Route::delete('/attendance-records/{attendanceRecord}', [AttendanceRecordController::class, 'destroy']);
});
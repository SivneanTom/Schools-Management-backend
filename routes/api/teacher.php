<?php

use App\Http\Controllers\Api\Teacher\TeacherController;
use Illuminate\Support\Facades\Route;

// Teacher self-service - keep before /teachers/{teacher}
Route::middleware(['auth:sanctum', 'role:TEACHER'])
    ->prefix('teachers/me')
    ->group(function () {
        Route::get('/', [TeacherController::class, 'me']);
        Route::get('/assignments/homework', [TeacherController::class, 'myHomework']);
        Route::get('/assignments', [TeacherController::class, 'myAssignments']);
        Route::get('/classes', [TeacherController::class, 'myClasses']);
        Route::get('/students', [TeacherController::class, 'myStudents']);
        Route::get('/timetable', [TeacherController::class, 'myTimetable']);
        Route::get('/attendance-sessions', [TeacherController::class, 'myAttendanceSessions']);
        Route::get('/exams', [TeacherController::class, 'myExams']);
        Route::get('/exam-results', [TeacherController::class, 'myExamResults']);
        Route::get('/submissions', [TeacherController::class, 'mySubmissions']);
        Route::get('/learning-materials', [TeacherController::class, 'myLearningMaterials']);
    });

// Teacher management
Route::middleware(['auth:sanctum', 'role:SUPER_ADMIN,ADMIN'])
    ->group(function () {
        Route::get('/teachers', [TeacherController::class, 'index']);
        Route::post('/teachers', [TeacherController::class, 'store']);

        Route::get('/teachers/{teacher}', [TeacherController::class, 'show'])
            ->whereNumber('teacher');

        Route::patch('/teachers/{teacher}', [TeacherController::class, 'update'])
            ->whereNumber('teacher');

        Route::patch('/teachers/{teacher}/status', [TeacherController::class, 'updateStatus'])
            ->whereNumber('teacher');

        Route::delete('/teachers/{teacher}', [TeacherController::class, 'destroy'])
            ->whereNumber('teacher');
    });
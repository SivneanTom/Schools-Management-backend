<?php

use App\Http\Controllers\Api\Exam\ExamController;
use App\Http\Controllers\Api\ExamSubject\ExamSubjectController;
use App\Http\Controllers\Api\ExamResult\ExamResultController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {

    // =====================================================
    // EXAMS
    // =====================================================

    Route::middleware(
        'role:SUPER_ADMIN,ADMIN,TEACHER'
    )->group(function () {
        Route::get('/exams', [ExamController::class, 'index']);
        Route::get('/exams/{exam}', [ExamController::class, 'show']);
    });

    /*
     * Your exams table has semester_id but no teacher_assignment_id.
     * Therefore keeping root Exam creation Admin-controlled is safer.
     */
    Route::middleware(
        'role:SUPER_ADMIN,ADMIN'
    )->group(function () {
        Route::post('/exams', [ExamController::class, 'store']);
        Route::put('/exams/{exam}', [ExamController::class, 'update']);
        Route::patch('/exams/{exam}', [ExamController::class, 'update']);
        Route::delete('/exams/{exam}', [ExamController::class, 'destroy']);
    });

    // =====================================================
    // EXAM SUBJECTS
    // Teacher can manage own teacher assignment only.
    // =====================================================

    Route::middleware(
        'role:SUPER_ADMIN,ADMIN,TEACHER'
    )->group(function () {
        Route::get('/exam-subjects', [ExamSubjectController::class, 'index']);
        Route::post('/exam-subjects', [ExamSubjectController::class, 'store']);
        Route::get('/exam-subjects/{examSubject}', [ExamSubjectController::class, 'show']);
        Route::put('/exam-subjects/{examSubject}', [ExamSubjectController::class, 'update']);
        Route::patch('/exam-subjects/{examSubject}', [ExamSubjectController::class, 'update']);
    });

    Route::middleware(
        'role:SUPER_ADMIN,ADMIN'
    )->delete(
        '/exam-subjects/{examSubject}',
        [ExamSubjectController::class, 'destroy']
    );

    // =====================================================
    // EXAM RESULTS
    // =====================================================

    Route::middleware(
        'role:SUPER_ADMIN,ADMIN,TEACHER'
    )->group(function () {
        Route::get('/exam-results', [ExamResultController::class, 'index']);
        Route::post('/exam-results', [ExamResultController::class, 'store']);
        Route::get('/exam-results/{examResult}', [ExamResultController::class, 'show']);
        Route::put('/exam-results/{examResult}', [ExamResultController::class, 'update']);
        Route::patch('/exam-results/{examResult}', [ExamResultController::class, 'update']);
        Route::post('/exam-results/{examResult}/publish', [ExamResultController::class, 'publish']);
        Route::post('/exam-results/{examResult}/unpublish', [ExamResultController::class, 'unpublish']);
    });

    Route::middleware(
        'role:SUPER_ADMIN,ADMIN'
    )->delete(
        '/exam-results/{examResult}',
        [ExamResultController::class, 'destroy']
    );
});
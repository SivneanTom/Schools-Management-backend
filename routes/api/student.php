<?php

use App\Http\Controllers\Api\Student\StudentController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {

    // =====================================================
    // STUDENT SELF-SERVICE
    // =====================================================

    Route::middleware('role:STUDENT')
        ->prefix('students/me')
        ->group(function () {
            Route::get('/', [StudentController::class, 'me']);
            Route::get('/parents', [StudentController::class, 'myParents']);
            Route::get('/enrollments', [StudentController::class, 'myEnrollments']);
            Route::get('/attendance', [StudentController::class, 'myAttendance']);
            Route::get('/exam-results', [StudentController::class, 'myExamResults']);
            Route::get('/assignments', [StudentController::class, 'myAssignments']);
            Route::get('/submissions', [StudentController::class, 'mySubmissions']);
            Route::get('/fees', [StudentController::class, 'myFees']);
            Route::get('/scholarships', [StudentController::class, 'myScholarships']);
            Route::get('/timetable', [StudentController::class, 'myTimetable']);
            Route::get('/learning-materials', [StudentController::class, 'myLearningMaterials']);
            Route::get('/invoices', [StudentController::class, 'myInvoices']);
            Route::get('/payments', [StudentController::class, 'myPayments']);
            Route::get('/receipts', [StudentController::class, 'myReceipts']);
        });

    // =====================================================
    // STUDENT READ
    // Services/Policies must still scope Teacher access.
    // =====================================================

    Route::middleware(
        'role:SUPER_ADMIN,ADMIN,TEACHER,ACCOUNTANT,LIBRARIAN'
    )->group(function () {
        Route::get('/students/status', [StudentController::class, 'status']);
        Route::get('/students', [StudentController::class, 'index']);
    });

    Route::get(
        '/students/{student}',
        [StudentController::class, 'show']
    )
        ->whereNumber('student')
        ->middleware(
            'role:SUPER_ADMIN,ADMIN,TEACHER,ACCOUNTANT,LIBRARIAN,PARENT'
        );

    // =====================================================
    // STUDENT MANAGEMENT
    // =====================================================

    Route::middleware(
        'role:SUPER_ADMIN,ADMIN'
    )->group(function () {
        Route::post('/students', [StudentController::class, 'store']);

        Route::patch(
            '/students/{student}',
            [StudentController::class, 'update']
        )->whereNumber('student');

        Route::patch(
            '/students/{student}/status',
            [StudentController::class, 'updateStatus']
        )->whereNumber('student');
    });
});
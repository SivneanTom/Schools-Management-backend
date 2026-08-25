
<?php

use App\Http\Controllers\Api\Parent\ParentController;
use Illuminate\Support\Facades\Route;

// =========================================================
// PARENT SELF-SERVICE
// =========================================================

Route::middleware([
    'auth:sanctum',
    'role:PARENT',
])->prefix('parents/me')->group(function () {
    Route::get('/', [ParentController::class, 'me']);
    Route::get('/children', [ParentController::class, 'myChildren']);

    Route::get(
        '/children/{student}',
        [ParentController::class, 'myChild']
    )->whereNumber('student');

    Route::get(
        '/children/{student}/enrollments',
        [ParentController::class, 'childEnrollments']
    )->whereNumber('student');

    Route::get(
        '/children/{student}/timetable',
        [ParentController::class, 'childTimetable']
    )->whereNumber('student');

    Route::get(
        '/children/{student}/attendance-records',
        [ParentController::class, 'childAttendanceRecords']
    )->whereNumber('student');

    Route::get(
        '/children/{student}/assignments',
        [ParentController::class, 'childAssignments']
    )->whereNumber('student');

    Route::get(
        '/children/{student}/submissions',
        [ParentController::class, 'childSubmissions']
    )->whereNumber('student');

    Route::get(
        '/children/{student}/exam-results',
        [ParentController::class, 'childExamResults']
    )->whereNumber('student');

    Route::get(
        '/children/{student}/fees',
        [ParentController::class, 'childFees']
    )->whereNumber('student');

    Route::get(
        '/children/{student}/scholarships',
        [ParentController::class, 'childScholarships']
    )->whereNumber('student');

    Route::get(
        '/children/{student}/invoices',
        [ParentController::class, 'childInvoices']
    )->whereNumber('student');

    Route::get(
        '/children/{student}/payments',
        [ParentController::class, 'childPayments']
    )->whereNumber('student');

    Route::get(
        '/children/{student}/receipts',
        [ParentController::class, 'childReceipts']
    )->whereNumber('student');
});

// =========================================================
// PARENT MANAGEMENT
// =========================================================

Route::middleware([
    'auth:sanctum',
    'role:SUPER_ADMIN,ADMIN',
])->group(function () {
    Route::get('/parents', [ParentController::class, 'index']);
    Route::post('/parents', [ParentController::class, 'store']);
    Route::get('/parents/{parent}', [ParentController::class, 'show']);
    Route::patch('/parents/{parent}', [ParentController::class, 'update']);
    Route::patch('/parents/{parent}/status', [ParentController::class, 'updateStatus']);

    Route::post(
        '/parents/{parent}/students/{student}',
        [ParentController::class, 'attachStudent']
    );

    Route::delete(
        '/parents/{parent}/students/{student}',
        [ParentController::class, 'detachStudent']
    );
});
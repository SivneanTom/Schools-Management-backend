<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Student\StudentController;
use GuzzleHttp\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\User\RoleController;
use App\Http\Controllers\Api\User\UserController;
use App\Http\Controllers\Api\Parent\ParentController;
use App\Http\Controllers\Api\Teacher\TeacherController;
use App\Http\Controllers\Api\Academic\AcademicYearController;
use App\Http\Controllers\Api\Academic\SemesterController;
use App\Http\Controllers\Api\Academic\GradeController;
use App\Http\Controllers\Api\Academic\SchoolClassController;
use App\Http\Controllers\Api\Academic\SubjectController;
use App\Http\Controllers\Api\Academic\GradeSubjectController;
use App\Http\Controllers\Api\Academic\EnrollmentController;
use App\Http\Controllers\Api\Academic\TeacherAssignmentController;

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

// Route FOR Parents

Route::middleware([
    'auth:sanctum',
    'role:SUPER_ADMIN,ADMIN,PRINCIPAL',
])->group(function () {

    Route::get('/parents', [
        ParentController::class,
        'index',
    ]);

    Route::post('/parents', [
        ParentController::class,
        'store',
    ]);

    Route::get('/parents/{parent}', [
        ParentController::class,
        'show',
    ]);

    Route::patch('/parents/{parent}', [
        ParentController::class,
        'update',
    ]);

    Route::patch('/parents/{parent}/status', [
        ParentController::class,
        'updateStatus',
    ]);

    Route::post(
        '/parents/{parent}/students/{student}',
        [
            ParentController::class,
            'attachStudent',
        ]
    );

    Route::delete(
        '/parents/{parent}/students/{student}',
        [
            ParentController::class,
            'detachStudent',
        ]
    );
});

// Route For Teacher

Route::middleware([
    'auth:sanctum',
    'role:SUPER_ADMIN,ADMIN,PRINCIPAL',
])->group(function () {

    Route::get('/teachers', [
        TeacherController::class,
        'index',
    ]);

    Route::post('/teachers', [
        TeacherController::class,
        'store',
    ]);

    Route::get('/teachers/{teacher}', [
        TeacherController::class,
        'show',
    ]);

    Route::patch('/teachers/{teacher}', [
        TeacherController::class,
        'update',
    ]);

    Route::patch('/teachers/{teacher}/status', [
        TeacherController::class,
        'updateStatus',
    ]);
});

//  Route for Academy Year
Route::middleware([
    'auth:sanctum',
    'role:SUPER_ADMIN,ADMIN,PRINCIPAL',
])->group(function () {

    Route::get('/academic-years', [
        AcademicYearController::class,
        'index',
    ]);

    Route::post('/academic-years', [
        AcademicYearController::class,
        'store',
    ]);

    Route::get('/academic-years/{academicYear}', [
        AcademicYearController::class,
        'show',
    ]);

    Route::patch('/academic-years/{academicYear}', [
        AcademicYearController::class,
        'update',
    ]);

    Route::patch(
        '/academic-years/{academicYear}/status',
        [
            AcademicYearController::class,
            'updateStatus',
        ]
    );
});

// Route for Semester

Route::middleware([
    'auth:sanctum',
    'role:SUPER_ADMIN,ADMIN,PRINCIPAL',
])->group(function () {

    Route::get('/semesters', [
        SemesterController::class,
        'index',
    ]);

    Route::post('/semesters', [
        SemesterController::class,
        'store',
    ]);

    Route::get('/semesters/{semester}', [
        SemesterController::class,
        'show',
    ]);

    Route::patch('/semesters/{semester}', [
        SemesterController::class,
        'update',
    ]);

    Route::patch('/semesters/{semester}/status', [
        SemesterController::class,
        'updateStatus',
    ]);
});

// Route for Grade

Route::middleware([
    'auth:sanctum',
    'role:SUPER_ADMIN,ADMIN,PRINCIPAL',
])->group(function () {

    Route::get('/grades', [
        GradeController::class,
        'index',
    ]);

    Route::post('/grades', [
        GradeController::class,
        'store',
    ]);

    Route::get('/grades/{grade}', [
        GradeController::class,
        'show',
    ]);

    Route::patch('/grades/{grade}', [
        GradeController::class,
        'update',
    ]);

    Route::patch('/grades/{grade}/status', [
        GradeController::class,
        'updateStatus',
    ]);
});

// Route for Class

Route::middleware([
    'auth:sanctum',
    'role:SUPER_ADMIN,ADMIN,PRINCIPAL',
])->group(function () {

    Route::get('/classes', [
        SchoolClassController::class,
        'index',
    ]);

    Route::post('/classes', [
        SchoolClassController::class,
        'store',
    ]);

    Route::get('/classes/{schoolClass}', [
        SchoolClassController::class,
        'show',
    ]);

    Route::patch('/classes/{schoolClass}', [
        SchoolClassController::class,
        'update',
    ]);

    Route::patch('/classes/{schoolClass}/status', [
        SchoolClassController::class,
        'updateStatus',
    ]);
});

// Route for Subject
Route::middleware([
    'auth:sanctum',
    'role:SUPER_ADMIN,ADMIN,PRINCIPAL',
])->group(function () {

    Route::get('/subjects', [
        SubjectController::class,
        'index',
    ]);

    Route::post('/subjects', [
        SubjectController::class,
        'store',
    ]);

    Route::get('/subjects/{subject}', [
        SubjectController::class,
        'show',
    ]);

    Route::patch('/subjects/{subject}', [
        SubjectController::class,
        'update',
    ]);

    Route::patch('/subjects/{subject}/status', [
        SubjectController::class,
        'updateStatus',
    ]);
});

// Grade Subject / Curriculum routes
Route::middleware([
    'auth:sanctum',
    'role:SUPER_ADMIN,ADMIN,PRINCIPAL',
])->group(function () {

    Route::get('/grade-subjects', [
        GradeSubjectController::class,
        'index',
    ]);

    Route::post('/grade-subjects', [
        GradeSubjectController::class,
        'store',
    ]);

    Route::get('/grade-subjects/{gradeSubject}', [
        GradeSubjectController::class,
        'show',
    ]);

    Route::patch('/grade-subjects/{gradeSubject}', [
        GradeSubjectController::class,
        'update',
    ]);

    Route::delete('/grade-subjects/{gradeSubject}', [
        GradeSubjectController::class,
        'destroy',
    ]);
});

// Put these inside routes/api.php.
Route::middleware([
    'auth:sanctum',
    'role:SUPER_ADMIN,ADMIN,PRINCIPAL',
])->group(function () {
    // Enrollments
    Route::get('/enrollments', [EnrollmentController::class, 'index']);
    Route::post('/enrollments', [EnrollmentController::class, 'store']);
    Route::get('/enrollments/{enrollment}', [EnrollmentController::class, 'show']);
    Route::patch('/enrollments/{enrollment}', [EnrollmentController::class, 'update']);
    Route::patch('/enrollments/{enrollment}/status', [EnrollmentController::class, 'updateStatus']);

    // Teacher Assignments
    Route::get('/teacher-assignments', [TeacherAssignmentController::class, 'index']);
    Route::post('/teacher-assignments', [TeacherAssignmentController::class, 'store']);
    Route::get('/teacher-assignments/{teacherAssignment}', [TeacherAssignmentController::class, 'show']);
    Route::patch('/teacher-assignments/{teacherAssignment}', [TeacherAssignmentController::class, 'update']);
    Route::patch('/teacher-assignments/{teacherAssignment}/status', [TeacherAssignmentController::class, 'updateStatus']);

});


<?php

use App\Http\Controllers\Api\Academic\AcademicYearController;
use App\Http\Controllers\Api\Academic\SemesterController;
use App\Http\Controllers\Api\Academic\GradeController;
use App\Http\Controllers\Api\Academic\SchoolClassController;
use App\Http\Controllers\Api\Academic\SubjectController;
use App\Http\Controllers\Api\Academic\GradeSubjectController;
use App\Http\Controllers\Api\Academic\EnrollmentController;
use App\Http\Controllers\Api\Academic\TeacherAssignmentController;
use App\Http\Controllers\Api\Room\RoomController;
use App\Http\Controllers\Api\Timetable\TimetableController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {

    // =====================================================
    // ACADEMIC MASTER DATA - READ
    // =====================================================

    Route::middleware(
        'role:SUPER_ADMIN,ADMIN,TEACHER,ACCOUNTANT,LIBRARIAN'
    )->group(function () {
        Route::get('/academic-years', [AcademicYearController::class, 'index']);
        Route::get('/academic-years/{academicYear}', [AcademicYearController::class, 'show']);

        Route::get('/semesters', [SemesterController::class, 'index']);
        Route::get('/semesters/{semester}', [SemesterController::class, 'show']);

        Route::get('/grades', [GradeController::class, 'index']);
        Route::get('/grades/{grade}', [GradeController::class, 'show']);

        Route::get('/classes', [SchoolClassController::class, 'index']);
        Route::get('/classes/{schoolClass}', [SchoolClassController::class, 'show']);

        Route::get('/subjects', [SubjectController::class, 'index']);
        Route::get('/subjects/{subject}', [SubjectController::class, 'show']);

        Route::get('/grade-subjects', [GradeSubjectController::class, 'index']);
        Route::get('/grade-subjects/{gradeSubject}', [GradeSubjectController::class, 'show']);
    });

    // =====================================================
    // ACADEMIC MASTER DATA - MANAGEMENT
    // =====================================================

    Route::middleware(
        'role:SUPER_ADMIN,ADMIN'
    )->group(function () {
        Route::post('/academic-years', [AcademicYearController::class, 'store']);
        Route::patch('/academic-years/{academicYear}', [AcademicYearController::class, 'update']);
        Route::patch('/academic-years/{academicYear}/status', [AcademicYearController::class, 'updateStatus']);

        Route::post('/semesters', [SemesterController::class, 'store']);
        Route::patch('/semesters/{semester}', [SemesterController::class, 'update']);
        Route::patch('/semesters/{semester}/status', [SemesterController::class, 'updateStatus']);

        Route::post('/grades', [GradeController::class, 'store']);
        Route::patch('/grades/{grade}', [GradeController::class, 'update']);
        Route::patch('/grades/{grade}/status', [GradeController::class, 'updateStatus']);
        Route::delete('/grades/{grade}', [GradeController::class, 'destroy']);

        Route::post('/classes', [SchoolClassController::class, 'store']);
        Route::patch('/classes/{schoolClass}', [SchoolClassController::class, 'update']);
        Route::patch('/classes/{schoolClass}/status', [SchoolClassController::class, 'updateStatus']);

        Route::post('/subjects', [SubjectController::class, 'store']);
        Route::patch('/subjects/{subject}', [SubjectController::class, 'update']);
        Route::patch('/subjects/{subject}/status', [SubjectController::class, 'updateStatus']);

        Route::post('/grade-subjects', [GradeSubjectController::class, 'store']);
        Route::patch('/grade-subjects/{gradeSubject}', [GradeSubjectController::class, 'update']);
        Route::delete('/grade-subjects/{gradeSubject}', [GradeSubjectController::class, 'destroy']);
    });

    // =====================================================
    // ENROLLMENTS - READ
    // =====================================================

    Route::middleware(
        'role:SUPER_ADMIN,ADMIN,TEACHER,ACCOUNTANT,LIBRARIAN'
    )->group(function () {
        Route::get('/enrollments', [EnrollmentController::class, 'index']);
        Route::get('/enrollments/{enrollment}', [EnrollmentController::class, 'show']);
    });

    // Enrollment management
    Route::middleware(
        'role:SUPER_ADMIN,ADMIN'
    )->group(function () {
        Route::post('/enrollments', [EnrollmentController::class, 'store']);
        Route::patch('/enrollments/{enrollment}', [EnrollmentController::class, 'update']);
        Route::patch('/enrollments/{enrollment}/status', [EnrollmentController::class, 'updateStatus']);
    });

    // =====================================================
    // TEACHER ASSIGNMENTS
    // =====================================================

    Route::middleware(
        'role:SUPER_ADMIN,ADMIN,TEACHER'
    )->group(function () {
        Route::get('/teacher-assignments', [TeacherAssignmentController::class, 'index']);
        Route::get('/teacher-assignments/{teacherAssignment}', [TeacherAssignmentController::class, 'show']);
    });

    Route::middleware(
        'role:SUPER_ADMIN,ADMIN'
    )->group(function () {
        Route::post('/teacher-assignments', [TeacherAssignmentController::class, 'store']);
        Route::patch('/teacher-assignments/{teacherAssignment}', [TeacherAssignmentController::class, 'update']);
        Route::patch('/teacher-assignments/{teacherAssignment}/status', [TeacherAssignmentController::class, 'updateStatus']);
    });

    // =====================================================
    // ROOMS
    // =====================================================

    Route::middleware(
        'role:SUPER_ADMIN,ADMIN,TEACHER'
    )->group(function () {
        Route::get('/rooms', [RoomController::class, 'index']);
        Route::get('/rooms/{room}', [RoomController::class, 'show']);
    });

    Route::middleware(
        'role:SUPER_ADMIN,ADMIN'
    )->group(function () {
        Route::post('/rooms', [RoomController::class, 'store']);
        Route::put('/rooms/{room}', [RoomController::class, 'update']);
        Route::patch('/rooms/{room}', [RoomController::class, 'update']);
        Route::delete('/rooms/{room}', [RoomController::class, 'destroy']);
    });

    // =====================================================
    // TIMETABLES
    // =====================================================

    Route::middleware(
        'role:SUPER_ADMIN,ADMIN,TEACHER'
    )->group(function () {
        Route::get('/timetables', [TimetableController::class, 'index']);
        Route::get('/timetables/{timetable}', [TimetableController::class, 'show']);
    });

    Route::middleware(
        'role:SUPER_ADMIN,ADMIN'
    )->group(function () {
        Route::post('/timetables', [TimetableController::class, 'store']);
        Route::put('/timetables/{timetable}', [TimetableController::class, 'update']);
        Route::patch('/timetables/{timetable}', [TimetableController::class, 'update']);
        Route::delete('/timetables/{timetable}', [TimetableController::class, 'destroy']);
    });
});
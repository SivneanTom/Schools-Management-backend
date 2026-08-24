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
use App\Http\Controllers\Api\Staff\StaffController;
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
use App\Http\Controllers\Api\AttendanceSession\AttendanceSessionController;
use App\Http\Controllers\Api\AttendanceRecord\AttendanceRecordController;
use App\Http\Controllers\Api\Exam\ExamController;
use App\Http\Controllers\Api\ExamSubject\ExamSubjectController;
use App\Http\Controllers\Api\ExamResult\ExamResultController;
use App\Http\Controllers\Api\Assignment\AssignmentController;
use App\Http\Controllers\Api\AssignmentSubmission\AssignmentSubmissionController;
use App\Http\Controllers\Api\LearningMaterial\LearningMaterialController;
use App\Http\Controllers\Api\FeeType\FeeTypeController;
use App\Http\Controllers\Api\StudentFee\StudentFeeController;
use App\Http\Controllers\Api\Scholarship\ScholarshipController;
use App\Http\Controllers\Api\StudentScholarship\StudentScholarshipController;
use App\Http\Controllers\Api\Invoice\InvoiceController;
use App\Http\Controllers\Api\InvoiceItem\InvoiceItemController;
use App\Http\Controllers\Api\PaymentMethod\PaymentMethodController;
use App\Http\Controllers\Api\Payment\PaymentController;
use App\Http\Controllers\Api\ReceiptController;

// Auth
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});
// Test Super Admin
Route::middleware(['auth:sanctum', 'role:SUPER_ADMIN',])->get('/test/super-admin', function () {
    return response()->json([
        'success' => true,
        'message' => 'You are allowed as SUPER_ADMIN.',
    ]);
});
// Role APIs
Route::middleware(['auth:sanctum', 'role:SUPER_ADMIN,ADMIN',])->group(function () {
    Route::get('/roles', [RoleController::class, 'index']);
    Route::get('/roles/{role}', [RoleController::class, 'show']);
});
// User APIs
Route::middleware(['auth:sanctum', 'role:SUPER_ADMIN,ADMIN',])->group(function () {
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::get('/users/{user}', [UserController::class, 'show']);
    Route::patch('/users/{user}/status', [UserController::class, 'updateStatus']);
});

// Student Routes
Route::middleware('auth:sanctum')->group(function () {

    // Student own profile
    Route::get('/students/me', [
        StudentController::class,
        'me'
    ])->middleware('role:STUDENT');

    // Student own parents
    Route::get('/students/me/parents', [
        StudentController::class,
        'myParents'
    ])->middleware('role:STUDENT');

    // Student status lookup/list
    Route::get('/students/status', [
        StudentController::class,
        'status'
    ])->middleware(
        'role:SUPER_ADMIN,ADMIN,TEACHER,ACCOUNTANT,LIBRARIAN'
    );

    // Student list
    Route::get('/students', [
        StudentController::class,
        'index'
    ])->middleware(
        'role:SUPER_ADMIN,ADMIN,TEACHER,ACCOUNTANT,LIBRARIAN'
    );

    // Student detail
    Route::get('/students/{student}', [
        StudentController::class,
        'show'
    ])
        ->whereNumber('student')
        ->middleware(
            'role:SUPER_ADMIN,ADMIN,TEACHER,ACCOUNTANT,LIBRARIAN,PARENT'
        );

    // Create student
    Route::post('/students', [
        StudentController::class,
        'store'
    ])->middleware(
        'role:SUPER_ADMIN,ADMIN'
    );

    // Update student
    Route::patch('/students/{student}', [
        StudentController::class,
        'update'
    ])
        ->whereNumber('student')
        ->middleware(
            'role:SUPER_ADMIN,ADMIN'
        );

    // Update student status
    Route::patch('/students/{student}/status', [
        StudentController::class,
        'updateStatus'
    ])
        ->whereNumber('student')
        ->middleware(
            'role:SUPER_ADMIN,ADMIN'
        );
    Route::get('/students/me/enrollments', [
        StudentController::class,
        'myEnrollments'
    ])->middleware('role:STUDENT');

    Route::get('/students/me/attendance', [
        StudentController::class,
        'myAttendance'
    ])->middleware('role:STUDENT');

    Route::get('/students/me/exam-results', [
        StudentController::class,
        'myExamResults'
    ])->middleware('role:STUDENT');

    Route::get('/students/me/assignments', [
        StudentController::class,
        'myAssignments'
    ])->middleware('role:STUDENT');

    Route::get('/students/me/submissions', [
        StudentController::class,
        'mySubmissions'
    ])->middleware('role:STUDENT');

    Route::get('/students/me/fees', [
        StudentController::class,
        'myFees'
    ])->middleware('role:STUDENT');

    Route::get('/students/me/scholarships', [
        StudentController::class,
        'myScholarships'
    ])->middleware('role:STUDENT');

    // Student own timetable
    Route::get('/students/me/timetable', [
        StudentController::class,
        'myTimetable'
    ])->middleware('role:STUDENT');

    // Student own learning materials
    Route::get('/students/me/learning-materials', [
        StudentController::class,
        'myLearningMaterials'
    ])->middleware('role:STUDENT');

    // Student own invoices
    Route::get('/students/me/invoices', [
        StudentController::class,
        'myInvoices'
    ])->middleware('role:STUDENT');

    // Student own payments
    Route::get('/students/me/payments', [
        StudentController::class,
        'myPayments'
    ])->middleware('role:STUDENT');

    // Student own receipts
    Route::get('/students/me/receipts', [
        StudentController::class,
        'myReceipts'
    ])->middleware('role:STUDENT');
});
// Route FOR Parents
Route::middleware(['auth:sanctum', 'role:SUPER_ADMIN,ADMIN,PRINCIPAL'])->group(function () {
    Route::get('/parents', [ParentController::class, 'index']);
    Route::post('/parents', [ParentController::class, 'store']);
    Route::get('/parents/{parent}', [ParentController::class, 'show']);
    Route::patch('/parents/{parent}', [ParentController::class, 'update']);
    Route::patch('/parents/{parent}/status', [ParentController::class, 'updateStatus']);
    Route::post('/parents/{parent}/students/{student}', [ParentController::class, 'attachStudent']);
    Route::delete('/parents/{parent}/students/{student}', [ParentController::class, 'detachStudent']);
});
// Route For Teacher
Route::middleware(['auth:sanctum', 'role:SUPER_ADMIN,ADMIN,PRINCIPAL'])->group(function () {
    Route::get('/teachers', [TeacherController::class, 'index']);
    Route::post('/teachers', [TeacherController::class, 'store']);
    Route::get('/teachers/{teacher}', [TeacherController::class, 'show']);
    Route::patch('/teachers/{teacher}', [TeacherController::class, 'update']);
    Route::patch('/teachers/{teacher}/status', [TeacherController::class, 'updateStatus']);
});
// Staff
Route::middleware(['auth:sanctum', 'role:SUPER_ADMIN,ADMIN,PRINCIPAL'])->group(function () {
    Route::get('/staff', [StaffController::class, 'index']);
    Route::post('/staff', [StaffController::class, 'store']);
    Route::get('/staff/{staff}', [StaffController::class, 'show']);
    Route::put('/staff/{staff}', [StaffController::class, 'update']);
    Route::patch('/staff/{staff}', [StaffController::class, 'update']);
    Route::delete('/staff/{staff}', [StaffController::class, 'destroy']);
});

//  Route for Academy Year
Route::middleware(['auth:sanctum', 'role:SUPER_ADMIN,ADMIN,PRINCIPAL'])->group(function () {
    Route::get('/academic-years', [AcademicYearController::class, 'index']);
    Route::post('/academic-years', [AcademicYearController::class, 'store']);
    Route::get('/academic-years/{academicYear}', [AcademicYearController::class, 'show']);
    Route::patch('/academic-years/{academicYear}', [AcademicYearController::class, 'update']);
    Route::patch('/academic-years/{academicYear}/status', [AcademicYearController::class, 'updateStatus']);
});

// Route for Semester

Route::middleware(['auth:sanctum', 'role:SUPER_ADMIN,ADMIN,PRINCIPAL'])->group(function () {
    Route::get('/semesters', [SemesterController::class, 'index']);
    Route::post('/semesters', [SemesterController::class, 'store']);
    Route::get('/semesters/{semester}', [SemesterController::class, 'show']);
    Route::patch('/semesters/{semester}', [SemesterController::class, 'update']);
    Route::patch('/semesters/{semester}/status', [SemesterController::class, 'updateStatus']);
});

// Route for Grade

Route::middleware(['auth:sanctum', 'role:SUPER_ADMIN,ADMIN,PRINCIPAL'])->group(function () {
    Route::get('/grades', [GradeController::class, 'index']);
    Route::post('/grades', [GradeController::class, 'store']);
    Route::get('/grades/{grade}', [GradeController::class, 'show']);
    Route::patch('/grades/{grade}', [GradeController::class, 'update']);
    Route::patch('/grades/{grade}/status', [GradeController::class, 'updateStatus']);
    Route::delete('/grades/{grade}', [GradeController::class, 'destroy']);
});

// Route for Class

Route::middleware(['auth:sanctum', 'role:SUPER_ADMIN,ADMIN,PRINCIPAL'])->group(function () {
    Route::get('/classes', [SchoolClassController::class, 'index']);
    Route::post('/classes', [SchoolClassController::class, 'store']);
    Route::get('/classes/{schoolClass}', [SchoolClassController::class, 'show']);
    Route::patch('/classes/{schoolClass}', [SchoolClassController::class, 'update']);
    Route::patch('/classes/{schoolClass}/status', [SchoolClassController::class, 'updateStatus']);
});

// Route for Subject
Route::middleware(['auth:sanctum', 'role:SUPER_ADMIN,ADMIN,PRINCIPAL'])->group(function () {
    Route::get('/subjects', [SubjectController::class, 'index']);
    Route::post('/subjects', [SubjectController::class, 'store']);
    Route::get('/subjects/{subject}', [SubjectController::class, 'show']);
    Route::patch('/subjects/{subject}', [SubjectController::class, 'update']);
    Route::patch('/subjects/{subject}/status', [SubjectController::class, 'updateStatus']);
});

// Grade Subject / Curriculum routes
Route::middleware(['auth:sanctum', 'role:SUPER_ADMIN,ADMIN,PRINCIPAL'])->group(function () {
    Route::get('/grade-subjects', [GradeSubjectController::class, 'index']);
    Route::post('/grade-subjects', [GradeSubjectController::class, 'store']);
    Route::get('/grade-subjects/{gradeSubject}', [GradeSubjectController::class, 'show']);
    Route::patch('/grade-subjects/{gradeSubject}', [GradeSubjectController::class, 'update']);
    Route::delete('/grade-subjects/{gradeSubject}', [GradeSubjectController::class, 'destroy']);
});

// Put these inside routes/api.php.
Route::middleware(['auth:sanctum', 'role:SUPER_ADMIN,ADMIN,PRINCIPAL'])->group(function () {
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

// Rooms

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/rooms', [RoomController::class, 'index']);
    Route::get('/rooms/{room}', [RoomController::class, 'show']);
    Route::middleware('role:SUPER_ADMIN,ADMIN,PRINCIPAL')->group(function () {
        Route::post('/rooms', [RoomController::class, 'store']);
        Route::put('/rooms/{room}', [RoomController::class, 'update']);
        Route::patch('/rooms/{room}', [RoomController::class, 'update']);
        Route::delete('/rooms/{room}', [RoomController::class, 'destroy']);
    });
});

// Time tables
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/timetables', [TimetableController::class, 'index']);
    Route::get('/timetables/{timetable}', [TimetableController::class, 'show']);
    Route::middleware('role:SUPER_ADMIN,ADMIN,PRINCIPAL')->group(function () {
        Route::post('/timetables', [TimetableController::class, 'store']);
        Route::put('/timetables/{timetable}', [TimetableController::class, 'update']);
        Route::patch('/timetables/{timetable}', [TimetableController::class, 'update']);
        Route::delete('/timetables/{timetable}', [TimetableController::class, 'destroy']);
    });
});
// Attendance Sessions

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/attendance-sessions', [AttendanceSessionController::class, 'index']);
    Route::get('/attendance-sessions/{attendanceSession}', [AttendanceSessionController::class, 'show']);
    Route::middleware('role:SUPER_ADMIN,ADMIN,PRINCIPAL,TEACHER')->group(function () {
        Route::post('/attendance-sessions', [AttendanceSessionController::class, 'store']);
        Route::put('/attendance-sessions/{attendanceSession}', [AttendanceSessionController::class, 'update']);
        Route::patch('/attendance-sessions/{attendanceSession}', [AttendanceSessionController::class, 'update']);
        Route::delete('/attendance-sessions/{attendanceSession}', [AttendanceSessionController::class, 'destroy']);
    });
});

/*-
| Attendance Records API
*/

Route::middleware(['auth:sanctum', 'role:SUPER_ADMIN,ADMIN,PRINCIPAL,TEACHER'])->group(function () {
    Route::get('/attendance-records', [AttendanceRecordController::class, 'index']);
    Route::post('/attendance-records', [AttendanceRecordController::class, 'store']);
    Route::get('/attendance-records/{attendanceRecord}', [AttendanceRecordController::class, 'show']);
    Route::put('/attendance-records/{attendanceRecord}', [AttendanceRecordController::class, 'update']);
    Route::patch('/attendance-records/{attendanceRecord}', [AttendanceRecordController::class, 'update']);
    Route::delete('/attendance-records/{attendanceRecord}', [AttendanceRecordController::class, 'destroy']);
});

// Exams

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/exams', [ExamController::class, 'index']);
    Route::get('/exams/{exam}', [ExamController::class, 'show']);
    Route::middleware('role:SUPER_ADMIN,ADMIN,PRINCIPAL,TEACHER')->group(function () {
        Route::post('/exams', [ExamController::class, 'store']);
        Route::put('/exams/{exam}', [ExamController::class, 'update']);
        Route::patch('/exams/{exam}', [ExamController::class, 'update']);
        Route::delete('/exams/{exam}', [ExamController::class, 'destroy']);
    });
});
// Exam Subject
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/exam-subjects', [ExamSubjectController::class, 'index']);
    Route::get('/exam-subjects/{examSubject}', [ExamSubjectController::class, 'show']);
    Route::middleware('role:SUPER_ADMIN,ADMIN,PRINCIPAL,TEACHER')->group(function () {
        Route::post('/exam-subjects', [ExamSubjectController::class, 'store']);
        Route::put('/exam-subjects/{examSubject}', [ExamSubjectController::class, 'update']);
        Route::patch('/exam-subjects/{examSubject}', [ExamSubjectController::class, 'update']);
        Route::delete('/exam-subjects/{examSubject}', [ExamSubjectController::class, 'destroy']);
    });
});
//  Exam Results
Route::middleware(['auth:sanctum', 'role:SUPER_ADMIN,ADMIN,PRINCIPAL,TEACHER'])->group(function () {
    Route::get('/exam-results', [ExamResultController::class, 'index']);
    Route::post('/exam-results', [ExamResultController::class, 'store']);
    Route::get('/exam-results/{examResult}', [ExamResultController::class, 'show']);
    Route::put('/exam-results/{examResult}', [ExamResultController::class, 'update']);
    Route::patch('/exam-results/{examResult}', [ExamResultController::class, 'update']);
    Route::post('/exam-results/{examResult}/publish', [ExamResultController::class, 'publish']);
    Route::post('/exam-results/{examResult}/unpublish', [ExamResultController::class, 'unpublish']);
    Route::delete('/exam-results/{examResult}', [ExamResultController::class, 'destroy']);
});

// Assignment and Assignment Submission and learning-materials Routes

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/assignments', [AssignmentController::class, 'index']);
    Route::get('/assignments/{assignment}', [AssignmentController::class, 'show']);
    Route::middleware('role:SUPER_ADMIN,ADMIN,PRINCIPAL,TEACHER')->group(function () {
        Route::post('/assignments', [AssignmentController::class, 'store']);
        Route::put('/assignments/{assignment}', [AssignmentController::class, 'update']);
        Route::patch('/assignments/{assignment}', [AssignmentController::class, 'update']);
        Route::delete('/assignments/{assignment}', [AssignmentController::class, 'destroy']);
    });
    Route::middleware('role:SUPER_ADMIN,ADMIN,PRINCIPAL,TEACHER,STUDENT')->group(function () {
        Route::get('/assignment-submissions', [AssignmentSubmissionController::class, 'index']);
        Route::post('/assignment-submissions', [AssignmentSubmissionController::class, 'store']);
        Route::get('/assignment-submissions/{assignmentSubmission}', [AssignmentSubmissionController::class, 'show']);
        Route::put('/assignment-submissions/{assignmentSubmission}', [AssignmentSubmissionController::class, 'update']);
        Route::patch('/assignment-submissions/{assignmentSubmission}', [AssignmentSubmissionController::class, 'update']);
        Route::delete('/assignment-submissions/{assignmentSubmission}', [AssignmentSubmissionController::class, 'destroy']);
    });
    Route::get('/learning-materials', [LearningMaterialController::class, 'index']);
    Route::get('/learning-materials/{learningMaterial}', [LearningMaterialController::class, 'show']);
    Route::middleware('role:SUPER_ADMIN,ADMIN,PRINCIPAL,TEACHER')->group(function () {
        Route::post('/learning-materials', [LearningMaterialController::class, 'store']);
        Route::put('/learning-materials/{learningMaterial}', [LearningMaterialController::class, 'update']);
        Route::patch('/learning-materials/{learningMaterial}', [LearningMaterialController::class, 'update']);
        Route::post('/learning-materials/{learningMaterial}/publish', [LearningMaterialController::class, 'publish']);
        Route::post('/learning-materials/{learningMaterial}/unpublish', [LearningMaterialController::class, 'unpublish']);
        Route::delete('/learning-materials/{learningMaterial}', [LearningMaterialController::class, 'destroy']);
    });
});
// Fee Types and Student Fees Routes
Route::apiResource('fee-types', FeeTypeController::class);
Route::apiResource('student-fees', StudentFeeController::class);

// Sholarship and Student Scholarship Routes
Route::apiResource('scholarships', ScholarshipController::class);
Route::apiResource('student-scholarships', StudentScholarshipController::class);

// Invoices Routes
Route::apiResource('invoices', InvoiceController::class);

Route::apiResource('invoice-items', InvoiceItemController::class);

Route::apiResource('payment-methods', PaymentMethodController::class);
Route::apiResource('payments', PaymentController::class);
Route::prefix('receipts')->group(function () {
    Route::get('/', [ReceiptController::class, 'index']);
    Route::post('/', [ReceiptController::class, 'store']);
    Route::get('/{id}', [ReceiptController::class, 'show']);
});

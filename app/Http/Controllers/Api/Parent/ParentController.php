<?php

namespace App\Http\Controllers\Api\Parent;

use App\Http\Controllers\Controller;
use App\Http\Requests\Parent\AttachStudentRequest;
use App\Http\Requests\Parent\StoreParentRequest;
use App\Http\Requests\Parent\UpdateParentRequest;
use App\Http\Requests\Parent\UpdateParentStatusRequest;
use App\Http\Resources\Parent\ParentListResource;
use App\Http\Resources\Parent\ParentResource;
use App\Models\ParentProfile;
use App\Models\Student;
use App\Services\Parent\ParentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Services\Enrollment\EnrollmentService;
use App\Services\Timetable\TimetableService;
use App\Services\AttendanceRecord\AttendanceRecordService;
use App\Services\Assignment\AssignmentService;
use App\Services\AssignmentSubmission\AssignmentSubmissionService;
use App\Services\ExamResult\ExamResultService;
use App\Services\StudentFee\StudentFeeService;
use App\Services\StudentScholarship\StudentScholarshipService;
use App\Services\Invoice\InvoiceService;
use App\Services\Payment\PaymentService;
use App\Services\Receipt\ReceiptService;

class ParentController extends Controller
{
    public function __construct(
        private readonly ParentService $parentService,
        private readonly EnrollmentService $enrollmentService,
        private readonly TimetableService $timetableService,
        private readonly AttendanceRecordService $attendanceRecordService,
        private readonly AssignmentService $assignmentService,
        private readonly AssignmentSubmissionService $assignmentSubmissionService,
        private readonly ExamResultService $examResultService,
        private readonly StudentFeeService $studentFeeService,
        private readonly StudentScholarshipService $studentScholarshipService,
        private readonly InvoiceService $invoiceService,
        private readonly PaymentService $paymentService,
        private readonly ReceiptService $receiptService
    ) {}

    public function index(Request $request): JsonResponse
    {
        Gate::authorize(
            'viewAny',
            ParentProfile::class
        );

        $parents = $this->parentService->paginate(
            $request->only([
                'search',
                'gender',
                'status',
                'size',
            ]),
            $request->user()
        );

        return response()->json([
            'success' => true,
            'data' => ParentListResource::collection(
                $parents->items()
            ),
            'pagination' => [
                'page' => $parents->currentPage() - 1,
                'size' => $parents->perPage(),
                'totalElements' => $parents->total(),
                'totalPages' => $parents->lastPage(),
            ],
        ]);
    }

    public function store(
        StoreParentRequest $request
    ): JsonResponse {
        Gate::authorize(
            'create',
            ParentProfile::class
        );

        $parent = $this->parentService->create(
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Parent created successfully.',
            'data' => new ParentResource($parent),
        ], 201);
    }

    public function show(
        ParentProfile $parent
    ): JsonResponse {
        Gate::authorize('view', $parent);

        $parent->load([
            'user.role',
            'students',
        ]);

        return response()->json([
            'success' => true,
            'data' => new ParentResource($parent),
        ]);
    }

    public function update(
        UpdateParentRequest $request,
        ParentProfile $parent
    ): JsonResponse {
        Gate::authorize('update', $parent);

        $parent = $this->parentService->update(
            $parent,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' =>
            'Parent updated successfully.',
            'data' => [
                'id' => $parent->id,
                'parentCode' =>
                $parent->parent_code,
                'firstNameKm' =>
                $parent->first_name_km,
                'lastNameKm' =>
                $parent->last_name_km,
                'firstNameEn' =>
                $parent->first_name_en,
                'lastNameEn' =>
                $parent->last_name_en,
                'gender' => $parent->gender,
                'phone' => $parent->phone,
                'addressKm' =>
                $parent->address_km,
                'addressEn' =>
                $parent->address_en,
                'status' => $parent->status,
                'user' => [
                    'id' => $parent->user?->id,
                    'username' =>
                    $parent->user?->username,
                    'email' =>
                    $parent->user?->email,
                    'preferredLanguage' =>
                    $parent->user
                        ?->preferred_language,
                ],
            ],
        ]);
    }

    public function updateStatus(
        UpdateParentStatusRequest $request,
        ParentProfile $parent
    ): JsonResponse {
        Gate::authorize('update', $parent);

        $parent =
            $this->parentService->updateStatus(
                $parent,
                $request->validated('status')
            );

        return response()->json([
            'success' => true,
            'message' =>
            'Parent status updated successfully.',
            'data' => [
                'id' => $parent->id,
                'status' => $parent->status,
                'userStatus' =>
                $parent->user?->status,
            ],
        ]);
    }

    public function attachStudent(
        AttachStudentRequest $request,
        ParentProfile $parent,
        Student $student
    ): JsonResponse {
        /*
         * Linking a child changes the Parent record.
         * Only roles allowed by ParentPolicy::update()
         * may perform this action.
         */
        Gate::authorize('update', $parent);

        $this->parentService->attachStudent(
            $parent,
            $student,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' =>
            'Student linked to parent successfully.',
            'data' => [
                'parentId' => $parent->id,
                'studentId' => $student->id,
                'relationship' =>
                $request->validated(
                    'relationship'
                ),
                'isPrimary' =>
                $request->boolean(
                    'isPrimary'
                ),
            ],
        ]);
    }

    public function detachStudent(
        ParentProfile $parent,
        Student $student
    ): JsonResponse {
        Gate::authorize('update', $parent);

        $this->parentService->detachStudent(
            $parent,
            $student
        );

        return response()->json([
            'success' => true,
            'message' =>
            'Student unlinked from parent successfully.',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Parent self-service
    |--------------------------------------------------------------------------
    */

    public function me(
        Request $request
    ): JsonResponse {
        $parent =
            $this->parentService->findByUserId(
                $request->user()->id
            );

        Gate::authorize('view', $parent);

        $parent->load([
            'user.role',
            'students',
        ]);

        return response()->json([
            'success' => true,
            'data' => new ParentResource($parent),
        ]);
    }

    public function myChildren(
        Request $request
    ): JsonResponse {
        $parent =
            $this->parentService->findByUserId(
                $request->user()->id
            );

        Gate::authorize('view', $parent);

        $children =
            $this->parentService->getMyChildren(
                $request->user()
            );

        return response()->json([
            'success' => true,
            'data' => $children,
        ]);
    }

    public function myChild(
        Request $request,
        Student $student
    ): JsonResponse {
        /*
         * StudentPolicy::view() must verify that
         * this student belongs to this parent.
         */
        Gate::authorize('view', $student);

        $child =
            $this->parentService->findMyChild(
                $request->user(),
                $student->id
            );

        return response()->json([
            'success' => true,
            'data' => $child,
        ]);
    }
    // Enrollment for parent role
    // public function childEnrollments(
    //     Request $request,
    //     Student $student
    // ): JsonResponse {
    //     return response()->json([
    //         'success' => true,
    //         'data' => $this->enrollmentService->getForParentChild($request->user(), $student->id),
    //     ]);
    // }

    public function childEnrollments(
    Request $request,
    Student $student
): JsonResponse {
    return response()->json([
        'success' => true,
        'data' => $this->enrollmentService
            ->getForParentChild(
                $request->user(),
                $student->id
            ),
    ]);
}

    // Time Tables
    public function childTimetable(
        Request $request,
        Student $student
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'data' => $this->timetableService->getForParentChild($request->user(), $student->id),
        ]);
    }

    public function childAttendanceRecords(
    Request $request,
    Student $student
): JsonResponse {
    Gate::authorize('view', $student);

    $records = $this
        ->attendanceRecordService
        ->getForParentChild(
            $request->user(),
            $student->id
        );

    return response()->json([
        'success' => true,
        'data' => $records,
    ]);
}

    public function childAssignments(
        Request $request,
        Student $student
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'data' => $this->assignmentService->getForParentChild($request->user(), $student->id),
        ]);
    }
    public function childSubmissions(
        Request $request,
        Student $student
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'data' => $this->assignmentSubmissionService
                ->getForParentChild(
                    $request->user(),
                    $student->id
                ),
        ]);
    }
    public function childExamResults(
        Request $request,
        Student $student
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'data' => $this->examResultService
                ->getForParentChild(
                    $request->user(),
                    $student->id
                ),
        ]);
    }
    public function childFees(
        Request $request,
        Student $student
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'data' => $this->studentFeeService
                ->getForParentChild(
                    $request->user(),
                    $student->id
                ),
        ]);
    }
    public function childScholarships(
        Request $request,
        Student $student
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'data' => $this->studentScholarshipService
                ->getForParentChild(
                    $request->user(),
                    $student->id
                ),
        ]);
    }
    public function childInvoices(
        Request $request,
        Student $student
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'data' => $this->invoiceService
                ->getForParentChild(
                    $request->user(),
                    $student->id
                ),
        ]);
    }
    public function childPayments(
        Request $request,
        Student $student
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'data' => $this->paymentService
                ->getForParentChild(
                    $request->user(),
                    $student->id
                ),
        ]);
    }
    public function childReceipts(
        Request $request,
        Student $student
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'data' => $this->receiptService
                ->getForParentChild(
                    $request->user(),
                    $student->id
                ),
        ]);
    }
}

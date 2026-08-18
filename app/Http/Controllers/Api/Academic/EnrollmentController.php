<?php

namespace App\Http\Controllers\Api\Academic;

use App\Http\Controllers\Controller;
use App\Http\Requests\Enrollment\StoreEnrollmentRequest;
use App\Http\Requests\Enrollment\UpdateEnrollmentRequest;
use App\Http\Requests\Enrollment\UpdateEnrollmentStatusRequest;
use App\Http\Resources\Enrollment\EnrollmentListResource;
use App\Http\Resources\Enrollment\EnrollmentResource;
use App\Models\Enrollment;
use App\Services\Enrollment\EnrollmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    public function __construct(
        private readonly EnrollmentService $enrollmentService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $enrollments = Enrollment::query()
            ->with(['student', 'schoolClass.grade', 'academicYear'])
            ->when($request->filled('studentId'), fn ($q) => $q->where('student_id', $request->integer('studentId')))
            ->when($request->filled('classId'), fn ($q) => $q->where('class_id', $request->integer('classId')))
            ->when($request->filled('academicYearId'), fn ($q) => $q->where('academic_year_id', $request->integer('academicYearId')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')->toString()))
            ->when($request->filled('gradeId'), function ($q) use ($request) {
                $q->whereHas('schoolClass', fn ($classQuery) => $classQuery->where('grade_id', $request->integer('gradeId')));
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->string('search')->toString();
                $q->whereHas('student', function ($studentQuery) use ($search) {
                    $studentQuery
                        ->where('student_code', 'ilike', "%{$search}%")
                        ->orWhere('first_name_km', 'ilike', "%{$search}%")
                        ->orWhere('last_name_km', 'ilike', "%{$search}%")
                        ->orWhere('first_name_en', 'ilike', "%{$search}%")
                        ->orWhere('last_name_en', 'ilike', "%{$search}%");
                });
            })
            ->orderByDesc('academic_year_id')
            ->orderBy('class_id')
            ->orderBy('student_id')
            ->paginate($request->integer('size', 20));

        return response()->json([
            'success' => true,
            'data' => EnrollmentListResource::collection($enrollments->items()),
            'pagination' => [
                'page' => $enrollments->currentPage() - 1,
                'size' => $enrollments->perPage(),
                'totalElements' => $enrollments->total(),
                'totalPages' => $enrollments->lastPage(),
            ],
        ]);
    }

    public function store(StoreEnrollmentRequest $request): JsonResponse
    {
        $enrollment = $this->enrollmentService->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Student enrolled successfully.',
            'data' => new EnrollmentResource($enrollment),
        ], 201);
    }

    public function show(Enrollment $enrollment): JsonResponse
    {
        $enrollment->load(['student', 'schoolClass.grade', 'schoolClass.academicYear', 'academicYear']);

        return response()->json([
            'success' => true,
            'data' => new EnrollmentResource($enrollment),
        ]);
    }

    public function update(UpdateEnrollmentRequest $request, Enrollment $enrollment): JsonResponse
    {
        $enrollment = $this->enrollmentService->update($enrollment, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Enrollment updated successfully.',
            'data' => new EnrollmentResource($enrollment),
        ]);
    }

    public function updateStatus(UpdateEnrollmentStatusRequest $request, Enrollment $enrollment): JsonResponse
    {
        $enrollment = $this->enrollmentService->updateStatus(
            $enrollment,
            $request->validated('status')
        );

        return response()->json([
            'success' => true,
            'message' => 'Enrollment status updated successfully.',
            'data' => ['id' => $enrollment->id, 'status' => $enrollment->status],
        ]);
    }
}

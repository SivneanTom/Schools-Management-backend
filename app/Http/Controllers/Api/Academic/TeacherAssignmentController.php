<?php

namespace App\Http\Controllers\Api\Academic;

use App\Http\Controllers\Controller;
use App\Http\Requests\TeacherAssignment\StoreTeacherAssignmentRequest;
use App\Http\Requests\TeacherAssignment\UpdateTeacherAssignmentRequest;
use App\Http\Requests\TeacherAssignment\UpdateTeacherAssignmentStatusRequest;
use App\Http\Resources\TeacherAssignment\TeacherAssignmentListResource;
use App\Http\Resources\TeacherAssignment\TeacherAssignmentResource;
use App\Models\TeacherAssignment;
use App\Services\TeacherAssignment\TeacherAssignmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeacherAssignmentController extends Controller
{
    public function __construct(
        private readonly TeacherAssignmentService $teacherAssignmentService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $assignments = TeacherAssignment::query()
            ->with(['teacher', 'schoolClass.grade', 'schoolClass.academicYear', 'subject', 'semester'])
            ->when($request->filled('teacherId'), fn ($q) => $q->where('teacher_id', $request->integer('teacherId')))
            ->when($request->filled('classId'), fn ($q) => $q->where('class_id', $request->integer('classId')))
            ->when($request->filled('subjectId'), fn ($q) => $q->where('subject_id', $request->integer('subjectId')))
            ->when($request->filled('semesterId'), fn ($q) => $q->where('semester_id', $request->integer('semesterId')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')->toString()))
            ->when($request->filled('gradeId'), function ($q) use ($request) {
                $q->whereHas('schoolClass', fn ($classQuery) => $classQuery->where('grade_id', $request->integer('gradeId')));
            })
            ->when($request->filled('academicYearId'), function ($q) use ($request) {
                $q->whereHas('schoolClass', fn ($classQuery) => $classQuery->where('academic_year_id', $request->integer('academicYearId')));
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->string('search')->toString();
                $q->where(function ($query) use ($search) {
                    $query
                        ->whereHas('teacher', function ($teacherQuery) use ($search) {
                            $teacherQuery
                                ->where('teacher_code', 'ilike', "%{$search}%")
                                ->orWhere('first_name_km', 'ilike', "%{$search}%")
                                ->orWhere('last_name_km', 'ilike', "%{$search}%")
                                ->orWhere('first_name_en', 'ilike', "%{$search}%")
                                ->orWhere('last_name_en', 'ilike', "%{$search}%");
                        })
                        ->orWhereHas('subject', function ($subjectQuery) use ($search) {
                            $subjectQuery
                                ->where('code', 'ilike', "%{$search}%")
                                ->orWhere('name_km', 'ilike', "%{$search}%")
                                ->orWhere('name_en', 'ilike', "%{$search}%");
                        });
                });
            })
            ->orderByDesc('semester_id')
            ->orderBy('class_id')
            ->orderBy('subject_id')
            ->paginate($request->integer('size', 20));

        return response()->json([
            'success' => true,
            'data' => TeacherAssignmentListResource::collection($assignments->items()),
            'pagination' => [
                'page' => $assignments->currentPage() - 1,
                'size' => $assignments->perPage(),
                'totalElements' => $assignments->total(),
                'totalPages' => $assignments->lastPage(),
            ],
        ]);
    }

    public function store(StoreTeacherAssignmentRequest $request): JsonResponse
    {
        $assignment = $this->teacherAssignmentService->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Teacher assigned successfully.',
            'data' => new TeacherAssignmentResource($assignment),
        ], 201);
    }

    public function show(TeacherAssignment $teacherAssignment): JsonResponse
    {
        $teacherAssignment->load([
            'teacher',
            'schoolClass.grade',
            'schoolClass.academicYear',
            'subject',
            'semester.academicYear',
        ]);

        return response()->json([
            'success' => true,
            'data' => new TeacherAssignmentResource($teacherAssignment),
        ]);
    }

    public function update(
        UpdateTeacherAssignmentRequest $request,
        TeacherAssignment $teacherAssignment
    ): JsonResponse {
        $teacherAssignment = $this->teacherAssignmentService->update(
            $teacherAssignment,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Teacher assignment updated successfully.',
            'data' => new TeacherAssignmentResource($teacherAssignment),
        ]);
    }

    public function updateStatus(
        UpdateTeacherAssignmentStatusRequest $request,
        TeacherAssignment $teacherAssignment
    ): JsonResponse {
        $teacherAssignment = $this->teacherAssignmentService->updateStatus(
            $teacherAssignment,
            $request->validated('status')
        );

        return response()->json([
            'success' => true,
            'message' => 'Teacher assignment status updated successfully.',
            'data' => ['id' => $teacherAssignment->id, 'status' => $teacherAssignment->status],
        ]);
    }
}

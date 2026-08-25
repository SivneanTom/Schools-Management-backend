<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreTeacherRequest;
use App\Http\Requests\Teacher\UpdateTeacherRequest;
use App\Http\Requests\Teacher\UpdateTeacherStatusRequest;
use App\Http\Resources\Teacher\TeacherListResource;
use App\Http\Resources\Teacher\TeacherResource;
use App\Models\Teacher;
use App\Services\Teacher\TeacherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TeacherController extends Controller
{
    public function __construct(
        private readonly TeacherService $teacherService
    ) {}

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Teacher::class);

        $teachers = $this->teacherService->paginate(
            $request->all(),
            $request->user()
        );

        return response()->json([
            'success' => true,
            'data' => TeacherListResource::collection($teachers->items()),
            'pagination' => [
                'page' => $teachers->currentPage() - 1,
                'size' => $teachers->perPage(),
                'totalElements' => $teachers->total(),
                'totalPages' => $teachers->lastPage(),
            ],
        ]);
    }

    public function store(StoreTeacherRequest $request): JsonResponse
    {
        Gate::authorize('create', Teacher::class);

        $teacher = $this->teacherService->create(
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Teacher created successfully.',
            'data' => new TeacherResource($teacher),
        ], 201);
    }

    public function show(Teacher $teacher): JsonResponse
    {
        Gate::authorize('view', $teacher);

        $teacher->load('user.role');

        return response()->json([
            'success' => true,
            'data' => new TeacherResource($teacher),
        ]);
    }

    public function update(
        UpdateTeacherRequest $request,
        Teacher $teacher
    ): JsonResponse {
        Gate::authorize('update', $teacher);

        $teacher = $this->teacherService->update(
            $teacher,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Teacher updated successfully.',
            'data' => [
                'id' => $teacher->id,
                'teacherCode' => $teacher->teacher_code,
                'phone' => $teacher->phone,
                'addressEn' => $teacher->address_en,
                'qualification' => $teacher->qualification,
                'specialization' => $teacher->specialization,
                'status' => $teacher->status,
            ],
        ]);
    }

    public function updateStatus(
        UpdateTeacherStatusRequest $request,
        Teacher $teacher
    ): JsonResponse {
        Gate::authorize('update', $teacher);

        $teacher = $this->teacherService->updateStatus(
            $teacher,
            $request->validated('status')
        );

        return response()->json([
            'success' => true,
            'message' => 'Teacher status updated successfully.',
            'data' => [
                'id' => $teacher->id,
                'status' => $teacher->status,
                'userStatus' => $teacher->user?->status,
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Teacher Self Service
    |--------------------------------------------------------------------------
    */

    public function me(Request $request): JsonResponse
    {
        $teacher = $this->teacherService->findByUserId(
            $request->user()->id
        );

        Gate::authorize('view', $teacher);

        $teacher->load('user.role');

        return response()->json([
            'success' => true,
            'data' => new TeacherResource($teacher),
        ]);
    }

    public function myAssignments(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->teacherService
                ->getMyAssignments($request->user()),
        ]);
    }

    public function myClasses(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->teacherService
                ->getMyClasses($request->user()),
        ]);
    }

    public function myStudents(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->teacherService
                ->getMyStudents($request->user()),
        ]);
    }

    public function myTimetable(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->teacherService
                ->getMyTimetable($request->user()),
        ]);
    }

    public function myAttendanceSessions(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->teacherService
                ->getMyAttendanceSessions($request->user()),
        ]);
    }

    public function myExams(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->teacherService
                ->getMyExams($request->user()),
        ]);
    }

    public function myExamResults(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->teacherService
                ->getMyExamResults($request->user()),
        ]);
    }

    public function myHomework(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->teacherService
                ->getMyHomework($request->user()),
        ]);
    }

    public function mySubmissions(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->teacherService
                ->getMySubmissions($request->user()),
        ]);
    }

    public function myLearningMaterials(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->teacherService
                ->getMyLearningMaterials($request->user()),
        ]);
    }
}
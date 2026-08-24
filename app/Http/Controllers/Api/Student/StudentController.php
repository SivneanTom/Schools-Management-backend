<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\StoreStudentRequest;
use App\Http\Requests\Student\UpdateStudentRequest;
use App\Http\Requests\Student\UpdateStudentStatusRequest;
use App\Http\Resources\Student\StudentResource;
use App\Models\Student;
use App\Services\Student\StudentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;


class StudentController extends Controller
{
    public function __construct(
        private readonly StudentService $studentService
    ) {}

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Student::class);
        $students = Student::query()
            ->with('user.role')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();

                $query->where(function ($query) use ($search) {
                    $query->where('student_code', 'ilike', "%{$search}%")
                        ->orWhere('first_name_km', 'ilike', "%{$search}%")
                        ->orWhere('last_name_km', 'ilike', "%{$search}%")
                        ->orWhere('first_name_en', 'ilike', "%{$search}%")
                        ->orWhere('last_name_en', 'ilike', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('email', 'ilike', "%{$search}%")
                                ->orWhere('username', 'ilike', "%{$search}%");
                        });
                });
            })
            ->when(
                $request->filled('gender'),
                fn($query) => $query->where('gender', $request->gender)
            )
            ->when(
                $request->filled('status'),
                fn($query) => $query->where('status', $request->status)
            )
            ->orderBy('id', 'asc')
            ->paginate($request->integer('size', 10));

        return response()->json([
            'success' => true,
            'data' => StudentResource::collection($students->items()),
            'pagination' => [
                'page' => $students->currentPage() - 1,
                'size' => $students->perPage(),
                'totalElements' => $students->total(),
                'totalPages' => $students->lastPage(),
            ],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $student = Student::query()
            ->with('user.role')
            ->where('user_id', $request->user()->id)
            ->first();
        Gate::authorize('view', $student);

        if (!$student) {
            return response()->json([
                'success' => false,
                'code' => 'STUDENT_NOT_FOUND',
                'message' => 'Student profile was not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new StudentResource($student),
        ]);
    }

    public function show(Student $student): JsonResponse
    {
        Gate::authorize('view', $student);
        $student->load('user.role');

        return response()->json([
            'success' => true,
            'data' => new StudentResource($student),
        ]);
    }

    public function store(StoreStudentRequest $request): JsonResponse
    {
        Gate::authorize('create', Student::class);
        $student = $this->studentService->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Student created successfully.',
            'data' => new StudentResource($student),
        ], 201);
    }

    public function update(UpdateStudentRequest $request, Student $student): JsonResponse
    {
        Gate::authorize('update', $student);
        $student = $this->studentService->update(
            $student,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Student updated successfully.',
            'data' => new StudentResource($student),
        ]);
    }

    public function updateStatus(
        UpdateStudentStatusRequest $request,
        Student $student
    ): JsonResponse {
        $student = $this->studentService->updateStatus(
            $student,
            $request->validated('status')
        );

        return response()->json([
            'success' => true,
            'message' => 'Student status updated successfully.',
            'data' => new StudentResource($student),
        ]);
    }

    public function status(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => Student::STATUSES,
        ]);
    }

    public function myParents(Request $request)
    {
        $student = $this->studentService
            ->findByUserId($request->user()->id);

        Gate::authorize('view', $student);

        $parents = $student->parents()
            ->get()
            ->map(function ($parent) {
                return [
                    'id' => $parent->id,
                    'parentCode' => $parent->parent_code,
                    'firstNameKm' => $parent->first_name_km,
                    'lastNameKm' => $parent->last_name_km,
                    'firstNameEn' => $parent->first_name_en,
                    'lastNameEn' => $parent->last_name_en,
                    'gender' => $parent->gender,
                    'phone' => $parent->phone,
                    'relationship' =>
                    $parent->pivot->relationship,
                    'isPrimary' =>
                    (bool) $parent->pivot->is_primary,
                    'status' => $parent->status,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $parents,
        ]);
    }

    public function myEnrollments(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->studentService
                ->getMyEnrollments($request->user()),
        ]);
    }

    public function myAttendance(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->studentService
                ->getMyAttendance($request->user()),
        ]);
    }

    public function myExamResults(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->studentService
                ->getMyExamResults($request->user()),
        ]);
    }

    public function mySubmissions(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->studentService
                ->getMySubmissions($request->user()),
        ]);
    }

    public function myFees(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->studentService
                ->getMyFees($request->user()),
        ]);
    }

    public function myScholarships(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->studentService
                ->getMyScholarships($request->user()),
        ]);
    }

    public function myAssignments(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->studentService
                ->getMyAssignments($request->user()),
        ]);
    }

    public function myTimetable(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->studentService
                ->getMyTimetable($request->user()),
        ]);
    }

    public function myLearningMaterials(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->studentService
                ->getMyLearningMaterials(
                    $request->user()
                ),
        ]);
    }

    public function myInvoices(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->studentService
                ->getMyInvoices($request->user()),
        ]);
    }

    public function myPayments(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->studentService
                ->getMyPayments($request->user()),
        ]);
    }

    public function myReceipts(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->studentService
                ->getMyReceipts($request->user()),
        ]);
    }
    
}
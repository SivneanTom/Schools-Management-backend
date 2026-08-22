<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\StoreStudentRequest;
use App\Http\Resources\Student\StudentResource;
use App\Services\Student\StudentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Http\Requests\Student\UpdateStudentStatusRequest;


// The Controller receives the API request from the frontend/Postman.
// Controller = Receive API request and return response

class StudentController extends Controller
{
    public function __construct(
        private readonly StudentService $studentService
    ) {}

    // Create Student

    public function store(
        StoreStudentRequest $request
    ): JsonResponse {

        $student = $this->studentService->create(
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Student created Successfully.',
            'data' => new StudentResource($student),
        ], 201);
    }

    // Get Student by Id

    public function show(Student $student): JsonResponse
    {
        $student->load('user.role');

        return response()->json([
            'success' => true,
            'data' => new StudentResource($student),
        ]);
    }

    // Update Student

    public function index(Request $request): JsonResponse
    {
        $students = Student::query()
            ->with('user.role')

            ->when(
                $request->filled('search'),
                function ($query) use ($request) {
                    $search = $request->string('search')->toString();

                    $query->where(function ($query) use ($search) {
                        $query
                            ->where('student_code', 'ilike', "%{$search}%")
                            ->orWhere('first_name_km', 'ilike', "%{$search}%")
                            ->orWhere('last_name_km', 'ilike', "%{$search}%")
                            ->orWhere('first_name_en', 'ilike', "%{$search}%")
                            ->orWhere('last_name_en', 'ilike', "%{$search}%")
                            ->orWhereHas('user', function ($userQuery) use ($search) {
                                $userQuery
                                    ->where('email', 'ilike', "%{$search}%")
                                    ->orWhere('username', 'ilike', "%{$search}%");
                            });
                    });
                }
            )

            ->when(
                $request->filled('gender'),
                fn($query) =>
                $query->where('gender', $request->gender)
            )

            ->when(
                $request->filled('status'),
                fn($query) =>
                $query->where('status', $request->status)
            )

            ->latest()
            ->paginate(
                $request->integer('size', 10)
            );

        return response()->json([
            'success' => true,

            'data' => StudentResource::collection(
                $students->items()
            ),

            'pagination' => [
                'page' => $students->currentPage() - 1,
                'size' => $students->perPage(),
                'totalElements' => $students->total(),
                'totalPages' => $students->lastPage(),
            ],
        ]);
    }

    // Update Student Status

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

    public function status()
    {
        return response()->json([
            'success' => true,
            'data' => Student::STATUSES,
        ]);
    }
}

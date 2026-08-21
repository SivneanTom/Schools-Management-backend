<?php

namespace App\Http\Controllers\Api\StudentScholarship;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentScholarship\StoreStudentScholarshipRequest;
use App\Http\Requests\StudentScholarship\UpdateStudentScholarshipRequest;
use App\Http\Resources\StudentScholarshipResource;
use App\Models\StudentScholarship;
use App\Services\StudentScholarship\StudentScholarshipService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentScholarshipController extends Controller
{
    public function __construct(
        private readonly StudentScholarshipService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $items = $this->service->paginate($request->only([
            'student_id',
            'scholarship_id',
            'academic_year_id',
            'status',
            'per_page',
        ]));

        return response()->json([
            'success' => true,
            'data' => StudentScholarshipResource::collection($items->items()),
            'pagination' => [
                'current_page' => $items->currentPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
                'last_page' => $items->lastPage(),
            ],
        ]);
    }

    public function store(StoreStudentScholarshipRequest $request): JsonResponse
    {
        $studentScholarship = $this->service->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Scholarship assigned to student successfully.',
            'data' => new StudentScholarshipResource($studentScholarship),
        ], 201);
    }

    public function show(StudentScholarship $studentScholarship): JsonResponse
    {
        $studentScholarship->load([
            'student',
            'scholarship',
            'academicYear',
        ]);

        return response()->json([
            'success' => true,
            'data' => new StudentScholarshipResource($studentScholarship),
        ]);
    }

    public function update(
        UpdateStudentScholarshipRequest $request,
        StudentScholarship $studentScholarship
    ): JsonResponse {
        $studentScholarship = $this->service->update(
            $studentScholarship,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Student scholarship updated successfully.',
            'data' => new StudentScholarshipResource($studentScholarship),
        ]);
    }

    public function destroy(StudentScholarship $studentScholarship): JsonResponse
    {
        $this->service->delete($studentScholarship);

        return response()->json([
            'success' => true,
            'message' => 'Student scholarship deleted successfully.',
        ]);
    }
}

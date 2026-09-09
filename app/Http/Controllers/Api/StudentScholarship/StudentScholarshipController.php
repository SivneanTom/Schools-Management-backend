<?php

namespace App\Http\Controllers\Api\StudentScholarship;

use App\Http\Controllers\Controller;
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
        $items = $this->service->paginate(
            $request->only([
                'student_id',
                'scholarship_id',
                'academic_year_id',
                'status',
                'per_page',
            ])
        );

        return response()->json([
            'success' => true,
            'data' => StudentScholarshipResource::collection(
                $items->items()
            ),
            'pagination' => [
                'current_page' => $items->currentPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
                'last_page' => $items->lastPage(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'student_id' => [
                'required',
                'integer',
                'exists:students,id',
            ],
            'scholarship_id' => [
                'required',
                'integer',
                'exists:scholarships,id',
            ],
            'academic_year_id' => [
                'required',
                'integer',
                'exists:academic_years,id',
            ],
            'awarded_at' => [
                'nullable',
                'date',
            ],
            'status' => [
                'sometimes',
                'in:ACTIVE,INACTIVE,CANCELLED',
            ],
        ]);

        $item = $this->service->create($data);

        return response()->json([
            'success' => true,
            'message' => 'Student scholarship created successfully.',
            'data' => new StudentScholarshipResource($item),
        ], 201);
    }

    public function show(
        StudentScholarship $studentScholarship
    ): JsonResponse {
        $studentScholarship->load([
            'student',
            'scholarship',
            'academicYear',
        ]);

        return response()->json([
            'success' => true,
            'data' => new StudentScholarshipResource(
                $studentScholarship
            ),
        ]);
    }

    public function update(
        Request $request,
        StudentScholarship $studentScholarship
    ): JsonResponse {
        $data = $request->validate([
            'student_id' => [
                'sometimes',
                'integer',
                'exists:students,id',
            ],
            'scholarship_id' => [
                'sometimes',
                'integer',
                'exists:scholarships,id',
            ],
            'academic_year_id' => [
                'sometimes',
                'integer',
                'exists:academic_years,id',
            ],
            'awarded_at' => [
                'sometimes',
                'date',
            ],
            'status' => [
                'sometimes',
                'in:ACTIVE,INACTIVE,CANCELLED',
            ],
        ]);

        $item = $this->service->update(
            $studentScholarship,
            $data
        );

        return response()->json([
            'success' => true,
            'message' => 'Student scholarship updated successfully.',
            'data' => new StudentScholarshipResource($item),
        ]);
    }

    public function destroy(
        StudentScholarship $studentScholarship
    ): JsonResponse {
        $this->service->delete($studentScholarship);

        return response()->json([
            'success' => true,
            'message' => 'Student scholarship deleted successfully.',
        ]);
    }
}
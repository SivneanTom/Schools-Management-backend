<?php

namespace App\Http\Controllers\Api\StudentFee;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentFee\StoreStudentFeeRequest;
use App\Http\Requests\StudentFee\UpdateStudentFeeRequest;
use App\Http\Resources\StudentFeeResource;
use App\Models\StudentFee;
use App\Services\StudentFee\StudentFeeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentFeeController extends Controller
{
    public function __construct(
        private readonly StudentFeeService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $items = $this->service->paginate($request->only([
            'student_id',
            'fee_type_id',
            'academic_year_id',
            'status',
            'due_from',
            'due_to',
            'per_page',
        ]));

        return response()->json([
            'success' => true,
            'data' => StudentFeeResource::collection($items->items()),
            'pagination' => [
                'current_page' => $items->currentPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
                'last_page' => $items->lastPage(),
            ],
        ]);
    }

    public function store(StoreStudentFeeRequest $request): JsonResponse
    {
        $studentFee = $this->service->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Student fee assigned successfully.',
            'data' => new StudentFeeResource($studentFee),
        ], 201);
    }

    public function show(StudentFee $studentFee): JsonResponse
    {
        $studentFee->load(['student', 'feeType', 'academicYear']);

        return response()->json([
            'success' => true,
            'data' => new StudentFeeResource($studentFee),
        ]);
    }

    public function update(UpdateStudentFeeRequest $request, StudentFee $studentFee): JsonResponse
    {
        $studentFee = $this->service->update($studentFee, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Student fee updated successfully.',
            'data' => new StudentFeeResource($studentFee),
        ]);
    }

    public function destroy(StudentFee $studentFee): JsonResponse
    {
        $this->service->delete($studentFee);

        return response()->json([
            'success' => true,
            'message' => 'Student fee deleted successfully.',
        ]);
    }
}

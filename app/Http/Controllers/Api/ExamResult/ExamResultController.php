<?php

namespace App\Http\Controllers\Api\ExamResult;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExamResult\StoreExamResultRequest;
use App\Http\Requests\ExamResult\UpdateExamResultRequest;
use App\Http\Resources\ExamResultResource;
use App\Models\ExamResult;
use App\Services\ExamResult\ExamResultService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExamResultController extends Controller
{
    public function __construct(
        private readonly ExamResultService $examResultService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'exam_subject_id' => [
                'nullable',
                'integer',
                'exists:exam_subjects,id',
            ],

            'student_id' => [
                'nullable',
                'integer',
                'exists:students,id',
            ],

            'exam_id' => [
                'nullable',
                'integer',
                'exists:exams,id',
            ],

            'grade' => [
                'nullable',
                'string',
                'max:20',
            ],

            'published' => [
                'nullable',
                'boolean',
            ],

            'per_page' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],
        ]);

        $rows =
            $this->examResultService
                ->getAll($filters);

        return response()->json([
            'message' =>
                'Exam results retrieved successfully.',

            'data' =>
                ExamResultResource::collection(
                    $rows->items()
                ),

            'meta' => [
                'current_page' => $rows->currentPage(),
                'last_page' => $rows->lastPage(),
                'per_page' => $rows->perPage(),
                'total' => $rows->total(),
            ],
        ]);
    }

    public function store(
        StoreExamResultRequest $request
    ): JsonResponse {
        $result =
            $this->examResultService
                ->create($request->validated());

        return response()->json([
            'message' =>
                'Exam result created successfully.',

            'data' =>
                new ExamResultResource($result),
        ], 201);
    }

    public function show(
        ExamResult $examResult
    ): JsonResponse {
        $examResult->load([
            'examSubject',
            'student',
        ]);

        return response()->json([
            'message' =>
                'Exam result retrieved successfully.',

            'data' =>
                new ExamResultResource($examResult),
        ]);
    }

    public function update(
        UpdateExamResultRequest $request,
        ExamResult $examResult
    ): JsonResponse {
        $result =
            $this->examResultService
                ->update(
                    $examResult,
                    $request->validated()
                );

        return response()->json([
            'message' =>
                'Exam result updated successfully.',

            'data' =>
                new ExamResultResource($result),
        ]);
    }

    public function publish(
        ExamResult $examResult
    ): JsonResponse {
        $result =
            $this->examResultService
                ->publish($examResult);

        return response()->json([
            'message' =>
                'Exam result published successfully.',

            'data' =>
                new ExamResultResource($result),
        ]);
    }

    public function unpublish(
        ExamResult $examResult
    ): JsonResponse {
        $result =
            $this->examResultService
                ->unpublish($examResult);

        return response()->json([
            'message' =>
                'Exam result unpublished successfully.',

            'data' =>
                new ExamResultResource($result),
        ]);
    }

    public function destroy(
        ExamResult $examResult
    ): JsonResponse {
        $this->examResultService
            ->delete($examResult);

        return response()->json([
            'message' =>
                'Exam result deleted successfully.',
        ]);
    }
}

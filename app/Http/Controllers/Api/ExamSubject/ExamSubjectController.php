<?php

namespace App\Http\Controllers\Api\ExamSubject;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExamSubject\StoreExamSubjectRequest;
use App\Http\Requests\ExamSubject\UpdateExamSubjectRequest;
use App\Http\Resources\ExamSubjectResource;
use App\Models\ExamSubject;
use App\Services\ExamSubject\ExamSubjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExamSubjectController extends Controller
{
    public function __construct(private readonly ExamSubjectService $service) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'exam_id' => ['nullable', 'integer', 'exists:exams,id'],
            'teacher_assignment_id' => ['nullable', 'integer', 'exists:teacher_assignments,id'],
            'teacher_id' => ['nullable', 'integer', 'exists:teachers,id'],
            'class_id' => ['nullable', 'integer', 'exists:classes,id'],
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'exam_date' => ['nullable', 'date_format:Y-m-d'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $rows = $this->service->getAll($filters);

        return response()->json([
            'message' => 'Exam subjects retrieved successfully.',
            'data' => ExamSubjectResource::collection($rows->items()),
            'meta' => [
                'current_page' => $rows->currentPage(),
                'last_page' => $rows->lastPage(),
                'per_page' => $rows->perPage(),
                'total' => $rows->total(),
            ],
        ]);
    }

    public function store(StoreExamSubjectRequest $request): JsonResponse
    {
        return response()->json([
            'message' => 'Exam subject created successfully.',
            'data' => new ExamSubjectResource($this->service->create($request->validated())),
        ], 201);
    }

    public function show(ExamSubject $examSubject): JsonResponse
    {
        return response()->json([
            'message' => 'Exam subject retrieved successfully.',
            'data' => new ExamSubjectResource($examSubject->load(['exam', 'teacherAssignment'])),
        ]);
    }

    public function update(UpdateExamSubjectRequest $request, ExamSubject $examSubject): JsonResponse
    {
        return response()->json([
            'message' => 'Exam subject updated successfully.',
            'data' => new ExamSubjectResource($this->service->update($examSubject, $request->validated())),
        ]);
    }

    public function destroy(ExamSubject $examSubject): JsonResponse
    {
        $this->service->delete($examSubject);

        return response()->json([
            'message' => 'Exam subject deleted successfully.',
        ]);
    }
}

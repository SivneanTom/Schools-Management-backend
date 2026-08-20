<?php

namespace App\Http\Controllers\Api\Assignment;

use App\Http\Controllers\Controller;
use App\Http\Requests\Assignment\StoreAssignmentRequest;
use App\Http\Requests\Assignment\UpdateAssignmentRequest;
use App\Http\Resources\AssignmentResource;
use App\Models\Assignment;
use App\Services\Assignment\AssignmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AssignmentController extends Controller
{
    public function __construct(private readonly AssignmentService $service) {}
    public function index(Request $r): JsonResponse
    {
        $f = $r->validate(['search' => ['nullable', 'string', 'max:255'], 'teacher_assignment_id' => ['nullable', 'integer', 'exists:teacher_assignments,id'], 'teacher_id' => ['nullable', 'integer', 'exists:teachers,id'], 'class_id' => ['nullable', 'integer', 'exists:classes,id'], 'subject_id' => ['nullable', 'integer', 'exists:subjects,id'], 'status' => ['nullable', Rule::in(['DRAFT', 'PUBLISHED', 'CLOSED', 'CANCELLED'])], 'per_page' => ['nullable', 'integer', 'min:1', 'max:100']]);
        $x = $this->service->getAll($f);
        return response()->json(['message' => 'Assignments retrieved successfully.', 'data' => AssignmentResource::collection($x->items()), 'meta' => ['current_page' => $x->currentPage(), 'last_page' => $x->lastPage(), 'per_page' => $x->perPage(), 'total' => $x->total()]]);
    }
    public function store(StoreAssignmentRequest $r): JsonResponse
    {
        return response()->json(['message' => 'Assignment created successfully.', 'data' => new AssignmentResource($this->service->create($r->validated()))], 201);
    }
    public function show(Assignment $assignment): JsonResponse
    {
        return response()->json(['message' => 'Assignment retrieved successfully.', 'data' => new AssignmentResource($assignment->load('teacherAssignment'))]);
    }
    public function update(UpdateAssignmentRequest $r, Assignment $assignment): JsonResponse
    {
        return response()->json(['message' => 'Assignment updated successfully.', 'data' => new AssignmentResource($this->service->update($assignment, $r->validated()))]);
    }
    public function destroy(Assignment $assignment): JsonResponse
    {
        $this->service->delete($assignment);
        return response()->json(['message' => 'Assignment deleted successfully.']);
    }
}

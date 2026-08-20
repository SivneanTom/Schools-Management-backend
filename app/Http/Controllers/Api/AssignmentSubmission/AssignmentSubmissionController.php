<?php

namespace App\Http\Controllers\Api\AssignmentSubmission;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssignmentSubmission\StoreAssignmentSubmissionRequest;
use App\Http\Requests\AssignmentSubmission\UpdateAssignmentSubmissionRequest;
use App\Http\Resources\AssignmentSubmissionResource;
use App\Models\AssignmentSubmission;
use App\Services\AssignmentSubmission\AssignmentSubmissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AssignmentSubmissionController extends Controller
{
    public function __construct(private readonly AssignmentSubmissionService $service) {}
    public function index(Request $r): JsonResponse
    {
        $f = $r->validate(['assignment_id' => ['nullable', 'integer', 'exists:assignments,id'], 'student_id' => ['nullable', 'integer', 'exists:students,id'], 'status' => ['nullable', Rule::in(['SUBMITTED', 'LATE', 'GRADED', 'RETURNED'])], 'per_page' => ['nullable', 'integer', 'min:1', 'max:100']]);
        $x = $this->service->getAll($f);
        return response()->json(['message' => 'Assignment submissions retrieved successfully.', 'data' => AssignmentSubmissionResource::collection($x->items()), 'meta' => ['current_page' => $x->currentPage(), 'last_page' => $x->lastPage(), 'per_page' => $x->perPage(), 'total' => $x->total()]]);
    }
    public function store(StoreAssignmentSubmissionRequest $r): JsonResponse
    {
        return response()->json(['message' => 'Assignment submission created successfully.', 'data' => new AssignmentSubmissionResource($this->service->create($r->validated()))], 201);
    }
    public function show(AssignmentSubmission $assignmentSubmission): JsonResponse
    {
        return response()->json(['message' => 'Assignment submission retrieved successfully.', 'data' => new AssignmentSubmissionResource($assignmentSubmission->load(['assignment', 'student']))]);
    }
    public function update(UpdateAssignmentSubmissionRequest $r, AssignmentSubmission $assignmentSubmission): JsonResponse
    {
        return response()->json(['message' => 'Assignment submission updated successfully.', 'data' => new AssignmentSubmissionResource($this->service->update($assignmentSubmission, $r->validated()))]);
    }
    public function destroy(AssignmentSubmission $assignmentSubmission): JsonResponse
    {
        $this->service->delete($assignmentSubmission);
        return response()->json(['message' => 'Assignment submission deleted successfully.']);
    }
}

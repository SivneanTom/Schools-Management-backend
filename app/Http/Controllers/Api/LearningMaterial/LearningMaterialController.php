<?php

namespace App\Http\Controllers\Api\LearningMaterial;

use App\Http\Controllers\Controller;
use App\Http\Requests\LearningMaterial\StoreLearningMaterialRequest;
use App\Http\Requests\LearningMaterial\UpdateLearningMaterialRequest;
use App\Http\Resources\LearningMaterialResource;
use App\Models\LearningMaterial;
use App\Services\LearningMaterial\LearningMaterialService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LearningMaterialController extends Controller
{
    public function __construct(private readonly LearningMaterialService $service) {}
    public function index(Request $r): JsonResponse
    {
        $f = $r->validate(['teacher_assignment_id' => ['nullable', 'integer', 'exists:teacher_assignments,id'], 'teacher_id' => ['nullable', 'integer', 'exists:teachers,id'], 'class_id' => ['nullable', 'integer', 'exists:classes,id'], 'subject_id' => ['nullable', 'integer', 'exists:subjects,id'], 'material_type' => ['nullable', Rule::in(['DOCUMENT', 'PDF', 'VIDEO', 'LINK', 'IMAGE', 'OTHER'])], 'published' => ['nullable', 'boolean'], 'per_page' => ['nullable', 'integer', 'min:1', 'max:100']]);
        $x = $this->service->getAll($f);
        return response()->json(['message' => 'Learning materials retrieved successfully.', 'data' => LearningMaterialResource::collection($x->items()), 'meta' => ['current_page' => $x->currentPage(), 'last_page' => $x->lastPage(), 'per_page' => $x->perPage(), 'total' => $x->total()]]);
    }
    public function store(StoreLearningMaterialRequest $r): JsonResponse
    {
        return response()->json(['message' => 'Learning material created successfully.', 'data' => new LearningMaterialResource($this->service->create($r->validated()))], 201);
    }
    public function show(LearningMaterial $learningMaterial): JsonResponse
    {
        return response()->json(['message' => 'Learning material retrieved successfully.', 'data' => new LearningMaterialResource($learningMaterial->load('teacherAssignment'))]);
    }
    public function update(UpdateLearningMaterialRequest $r, LearningMaterial $learningMaterial): JsonResponse
    {
        return response()->json(['message' => 'Learning material updated successfully.', 'data' => new LearningMaterialResource($this->service->update($learningMaterial, $r->validated()))]);
    }
    public function publish(LearningMaterial $learningMaterial): JsonResponse
    {
        return response()->json(['message' => 'Learning material published successfully.', 'data' => new LearningMaterialResource($this->service->publish($learningMaterial))]);
    }
    public function unpublish(LearningMaterial $learningMaterial): JsonResponse
    {
        return response()->json(['message' => 'Learning material unpublished successfully.', 'data' => new LearningMaterialResource($this->service->unpublish($learningMaterial))]);
    }
    public function destroy(LearningMaterial $learningMaterial): JsonResponse
    {
        $this->service->delete($learningMaterial);
        return response()->json(['message' => 'Learning material deleted successfully.']);
    }
}

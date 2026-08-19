<?php
namespace App\Http\Controllers\Api\Exam;

use App\Http\Controllers\Controller;
use App\Http\Requests\Exam\StoreExamRequest;
use App\Http\Requests\Exam\UpdateExamRequest;
use App\Http\Resources\ExamResource;
use App\Models\Exam;
use App\Services\Exam\ExamService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExamController extends Controller
{
    public function __construct(private readonly ExamService $service) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'search'=>['nullable','string','max:255'],
            'semester_id'=>['nullable','integer','exists:semesters,id'],
            'exam_type'=>['nullable',Rule::in(['MIDTERM','FINAL','QUIZ','OTHER'])],
            'status'=>['nullable',Rule::in(['DRAFT','SCHEDULED','ONGOING','COMPLETED','CANCELLED'])],
            'date_from'=>['nullable','date_format:Y-m-d'],
            'date_to'=>['nullable','date_format:Y-m-d','after_or_equal:date_from'],
            'per_page'=>['nullable','integer','min:1','max:100'],
        ]);

        $rows = $this->service->getAll($filters);

        return response()->json([
            'message'=>'Exams retrieved successfully.',
            'data'=>ExamResource::collection($rows->items()),
            'meta'=>[
                'current_page'=>$rows->currentPage(),
                'last_page'=>$rows->lastPage(),
                'per_page'=>$rows->perPage(),
                'total'=>$rows->total(),
            ]
        ]);
    }

    public function store(StoreExamRequest $request): JsonResponse
    {
        return response()->json([
            'message'=>'Exam created successfully.',
            'data'=>new ExamResource($this->service->create($request->validated()))
        ],201);
    }

    public function show(Exam $exam): JsonResponse
    {
        return response()->json([
            'message'=>'Exam retrieved successfully.',
            'data'=>new ExamResource($exam->load('semester'))
        ]);
    }

    public function update(UpdateExamRequest $request, Exam $exam): JsonResponse
    {
        return response()->json([
            'message'=>'Exam updated successfully.',
            'data'=>new ExamResource($this->service->update($exam,$request->validated()))
        ]);
    }

    public function destroy(Exam $exam): JsonResponse
    {
        $this->service->delete($exam);
        return response()->json(['message'=>'Exam deleted successfully.']);
    }
}

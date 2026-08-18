<?php

namespace App\Http\Controllers\Api\Academic;

use App\Http\Controllers\Controller;
use App\Http\Requests\GradeSubject\StoreGradeSubjectRequest;
use App\Http\Requests\GradeSubject\UpdateGradeSubjectRequest;
use App\Http\Resources\GradeSubject\GradeSubjectListResource;
use App\Http\Resources\GradeSubject\GradeSubjectResource;
use App\Models\GradeSubject;
use App\Services\GradeSubject\GradeSubjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GradeSubjectController extends Controller
{
    public function __construct(
        private readonly GradeSubjectService $gradeSubjectService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $gradeSubjects = GradeSubject::query()
            ->with([
                'grade',
                'subject',
            ])

            ->when(
                $request->filled('gradeId'),
                fn ($query) => $query->where(
                    'grade_id',
                    $request->integer('gradeId')
                )
            )

            ->when(
                $request->filled('subjectId'),
                fn ($query) => $query->where(
                    'subject_id',
                    $request->integer('subjectId')
                )
            )

            ->when(
                $request->has('isRequired'),
                fn ($query) => $query->where(
                    'is_required',
                    $request->boolean('isRequired')
                )
            )

            ->when(
                $request->filled('search'),
                function ($query) use ($request) {
                    $search = $request
                        ->string('search')
                        ->toString();

                    $query->where(function ($query) use ($search) {
                        $query
                            ->whereHas('grade', function ($gradeQuery) use ($search) {
                                $gradeQuery
                                    ->where('code', 'ilike', "%{$search}%")
                                    ->orWhere('name_km', 'ilike', "%{$search}%")
                                    ->orWhere('name_en', 'ilike', "%{$search}%");
                            })
                            ->orWhereHas('subject', function ($subjectQuery) use ($search) {
                                $subjectQuery
                                    ->where('code', 'ilike', "%{$search}%")
                                    ->orWhere('name_km', 'ilike', "%{$search}%")
                                    ->orWhere('name_en', 'ilike', "%{$search}%");
                            });
                    });
                }
            )

            ->join('grades', 'grade_subjects.grade_id', '=', 'grades.id')
            ->join('subjects', 'grade_subjects.subject_id', '=', 'subjects.id')
            ->select('grade_subjects.*')
            ->orderByRaw('grades.order_no ASC NULLS LAST')
            ->orderBy('subjects.name_en')
            ->paginate(
                $request->integer('size', 160)
            );

        return response()->json([
            'success' => true,

            'data' =>
                GradeSubjectListResource::collection(
                    $gradeSubjects->items()
                ),

            'pagination' => [
                'page' => $gradeSubjects->currentPage() - 1,
                'size' => $gradeSubjects->perPage(),
                'totalElements' => $gradeSubjects->total(),
                'totalPages' => $gradeSubjects->lastPage(),
            ],
        ]);
    }

    public function store(
        StoreGradeSubjectRequest $request
    ): JsonResponse {
        $gradeSubject = $this->gradeSubjectService->create(
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Subject assigned to grade successfully.',
            'data' => new GradeSubjectResource($gradeSubject),
        ], 201);
    }

    public function show(
        GradeSubject $gradeSubject
    ): JsonResponse {
        $gradeSubject->load([
            'grade',
            'subject',
        ]);

        return response()->json([
            'success' => true,
            'data' => new GradeSubjectResource($gradeSubject),
        ]);
    }

    public function update(
        UpdateGradeSubjectRequest $request,
        GradeSubject $gradeSubject
    ): JsonResponse {
        $gradeSubject = $this->gradeSubjectService->update(
            $gradeSubject,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Grade subject updated successfully.',
            'data' => new GradeSubjectResource($gradeSubject),
        ]);
    }

    public function destroy(
        GradeSubject $gradeSubject
    ): JsonResponse {
        $this->gradeSubjectService->delete($gradeSubject);

        return response()->json([
            'success' => true,
            'message' => 'Subject removed from grade successfully.',
        ]);
    }
}

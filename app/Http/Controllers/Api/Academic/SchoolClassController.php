<?php

namespace App\Http\Controllers\Api\Academic;

use App\Http\Controllers\Controller;
use App\Http\Requests\SchoolClass\StoreSchoolClassRequest;
use App\Http\Requests\SchoolClass\UpdateSchoolClassRequest;
use App\Http\Requests\SchoolClass\UpdateSchoolClassStatusRequest;
use App\Http\Resources\SchoolClass\SchoolClassListResource;
use App\Http\Resources\SchoolClass\SchoolClassResource;
use App\Models\SchoolClass;
use App\Services\SchoolClass\SchoolClassService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Grade;

class SchoolClassController extends Controller
{

    public function __construct(
        private readonly SchoolClassService $schoolClassService
    ) {}



    public function index(Request $request): JsonResponse
    {

        $classes = SchoolClass::query()

            ->with([
                'grade',
                'academicYear',
                'homeroomTeacher',
            ])

            ->when(
                $request->filled('search'),
                function ($query) use ($request) {

                    $search =
                        $request
                        ->string('search')
                        ->toString();

                    $query->where(function ($query) use ($search) {

                        $query
                            ->where(
                                'name_km',
                                'ilike',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'name_en',
                                'ilike',
                                "%{$search}%"
                            );
                    });
                }
            )


            ->when(
                $request->filled('gradeId'),
                fn($query) =>
                $query->where(
                    'grade_id',
                    $request->integer('gradeId')
                )
            )


            ->when(
                $request->filled('academicYearId'),
                fn($query) =>
                $query->where(
                    'academic_year_id',
                    $request->integer('academicYearId')
                )
            )


            ->when(
                $request->filled('homeroomTeacherId'),
                fn($query) =>
                $query->where(
                    'homeroom_teacher_id',
                    $request->integer('homeroomTeacherId')
                )
            )


            ->when(
                $request->filled('status'),
                fn($query) =>
                $query->where(
                    'status',
                    $request->status
                )
            )

            ->orderBy(
                'academic_year_id',
                'desc'
            )

            ->orderBy(
                Grade::select('order_no')
                    ->whereColumn(
                        'grades.id',
                        'classes.grade_id'
                    )
            )

            ->orderBy(
                'name_en'
            )

            ->paginate(
                $request->integer('size', 20)
            );



        return response()->json([

            'success' => true,

            'data' =>
            SchoolClassListResource::collection(
                $classes->items()
            ),

            'pagination' => [

                'page' =>
                $classes->currentPage() - 1,

                'size' =>
                $classes->perPage(),

                'totalElements' =>
                $classes->total(),

                'totalPages' =>
                $classes->lastPage(),

            ],

        ]);
    }





    public function store(
        StoreSchoolClassRequest $request
    ): JsonResponse {


        $schoolClass =
            $this->schoolClassService->create(
                $request->validated()
            );


        return response()->json([

            'success' => true,

            'message' =>
            'Class created successfully.',

            'data' =>
            new SchoolClassResource(
                $schoolClass
            ),

        ], 201);
    }





    public function show(
        SchoolClass $schoolClass
    ): JsonResponse {


        $schoolClass->load([

            'grade',

            'academicYear',

            'homeroomTeacher',

        ]);



        return response()->json([

            'success' => true,

            'data' =>
            new SchoolClassResource(
                $schoolClass
            ),

        ]);
    }





    public function update(
        UpdateSchoolClassRequest $request,
        SchoolClass $schoolClass
    ): JsonResponse {


        $schoolClass =
            $this->schoolClassService->update(
                $schoolClass,
                $request->validated()
            );



        return response()->json([

            'success' => true,

            'message' =>
            'Class updated successfully.',

            'data' =>
            new SchoolClassResource(
                $schoolClass
            ),

        ]);
    }





    public function updateStatus(
        UpdateSchoolClassStatusRequest $request,
        SchoolClass $schoolClass
    ): JsonResponse {


        $schoolClass =
            $this->schoolClassService->updateStatus(
                $schoolClass,
                $request->validated('status')
            );


        return response()->json([

            'success' => true,

            'message' =>
            'Class status updated successfully.',

            'data' => [

                'id' =>
                $schoolClass->id,

                'status' =>
                $schoolClass->status,

            ],

        ]);
    }
}

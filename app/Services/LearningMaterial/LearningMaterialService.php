<?php

namespace App\Services\LearningMaterial;

use App\Models\LearningMaterial;
use App\Models\TeacherAssignment;
use App\Models\User;
use App\Services\Parent\ParentService;
use App\Services\TeacherAssignment\TeacherAssignmentService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LearningMaterialService
{
    public function __construct(
        private readonly TeacherAssignmentService $teacherAssignmentService,
        private readonly ParentService $parentService
    ) {}

    public function getAll(array $f = [], ?User $user = null): LengthAwarePaginator
    {
        $q = LearningMaterial::query()->with([
            'teacherAssignment.subject',
            'teacherAssignment.schoolClass',
        ]);

        if ($user) $q = $this->applyAccessScope($q, $user);

        $q->when(
            $f['teacher_assignment_id'] ?? null,
            fn(Builder $q, $id) => $q->where('teacher_assignment_id', $id)
        );

        $q->when(
            $f['teacher_id'] ?? null,
            fn(Builder $q, $id) => $q->whereHas(
                'teacherAssignment',
                fn(Builder $x) => $x->where('teacher_id', $id)
            )
        );

        $q->when(
            $f['class_id'] ?? null,
            fn(Builder $q, $id) => $q->whereHas(
                'teacherAssignment',
                fn(Builder $x) => $x->where('class_id', $id)
            )
        );

        $q->when(
            $f['subject_id'] ?? null,
            fn(Builder $q, $id) => $q->whereHas(
                'teacherAssignment',
                fn(Builder $x) => $x->where('subject_id', $id)
            )
        );

        $q->when(
            $f['material_type'] ?? null,
            fn(Builder $q, $v) => $q->where('material_type', $v)
        );

        $q->when(array_key_exists('published', $f), function (Builder $q) use ($f) {
            $f['published']
                ? $q->whereNotNull('published_at')
                : $q->whereNull('published_at');
        });

        $result = $q->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate($f['per_page'] ?? 15);

        if ($user?->role?->code === 'TEACHER') {
            $result->setCollection(
                $result->getCollection()->map(fn($m) => $this->teacherResponse($m))
            );
        }

        return $result;
    }

    public function create(array $d, User $user): LearningMaterial
    {
        return DB::transaction(function () use ($d, $user) {
            $this->validateBusinessRules($d);

            $ta = TeacherAssignment::findOrFail($d['teacher_assignment_id']);
            $this->teacherAssignmentService->ensureTeacherOwnsAssignment($user, $ta);

            return LearningMaterial::create($d)
                ->load('teacherAssignment');
        });
    }

    public function update(
        LearningMaterial $m,
        array $d,
        User $user
    ): LearningMaterial {
        return DB::transaction(function () use ($m, $d, $user) {
            $m->loadMissing('teacherAssignment');

            $this->teacherAssignmentService
                ->ensureTeacherOwnsAssignment($user, $m->teacherAssignment);

            $merged = array_merge([
                'teacher_assignment_id' => $m->teacher_assignment_id,
            ], $d);

            $this->validateBusinessRules($merged);

            if (isset($d['teacher_assignment_id'])) {
                $newTa = TeacherAssignment::findOrFail($d['teacher_assignment_id']);
                $this->teacherAssignmentService
                    ->ensureTeacherOwnsAssignment($user, $newTa);
            }

            $m->update($d);

            return $m->refresh()->load('teacherAssignment');
        });
    }

    public function publish(
        LearningMaterial $m,
        User $user
    ): LearningMaterial {
        $m->loadMissing('teacherAssignment');

        $this->teacherAssignmentService
            ->ensureTeacherOwnsAssignment($user, $m->teacherAssignment);

        $m->update(['published_at' => now()]);

        return $m->refresh()->load('teacherAssignment');
    }

    public function unpublish(
        LearningMaterial $m,
        User $user
    ): LearningMaterial {
        $m->loadMissing('teacherAssignment');

        $this->teacherAssignmentService
            ->ensureTeacherOwnsAssignment($user, $m->teacherAssignment);

        $m->update(['published_at' => null]);

        return $m->refresh()->load('teacherAssignment');
    }

    public function delete(
        LearningMaterial $m,
        User $user
    ): void {
        DB::transaction(function () use ($m, $user) {
            $m->loadMissing('teacherAssignment');

            $this->teacherAssignmentService
                ->ensureTeacherOwnsAssignment($user, $m->teacherAssignment);

            $m->delete();
        });
    }

    public function applyAccessScope(
        Builder $query,
        User $user
    ): Builder {
        $role = $user->role->code;

        if (in_array($role, ['SUPER_ADMIN', 'ADMIN'], true)) return $query;

        if ($role === 'TEACHER') {
            return $query->whereHas(
                'teacherAssignment.teacher',
                fn(Builder $q) => $q->where('user_id', $user->id)
            );
        }

        if ($role === 'STUDENT') {
            return $query
                ->whereNotNull('published_at')
                ->whereHas(
                    'teacherAssignment.schoolClass.enrollments.student',
                    fn(Builder $q) => $q->where('user_id', $user->id)
                );
        }

        return $query->whereRaw('1 = 0');
    }

    private function validateBusinessRules(array $d): void
    {
        $ta = TeacherAssignment::findOrFail($d['teacher_assignment_id']);

        if (
            isset($ta->status)
            && strtoupper((string) $ta->status) !== 'ACTIVE'
        ) {
            throw ValidationException::withMessages([
                'teacher_assignment_id' => [
                    'The selected teacher assignment is not active.',
                ],
            ]);
        }
    }

    private function teacherResponse(LearningMaterial $m): array
    {
        $ta = $m->teacherAssignment;
        $subject = $ta?->subject;
        $class = $ta?->schoolClass;

        return [
            'id' => $m->id,
            'titleKm' => $m->title_km,
            'titleEn' => $m->title_en,
            'descriptionKm' => $m->description_km,
            'descriptionEn' => $m->description_en,
            'materialType' => $m->material_type,
            'fileUrl' => $m->file_url,
            'publishedAt' => $m->published_at,
            'subject' => $subject ? [
                'id' => $subject->id,
                'code' => $subject->code,
                'nameKm' => $subject->name_km,
                'nameEn' => $subject->name_en,
            ] : null,
            'class' => $class ? [
                'id' => $class->id,
                'nameKm' => $class->name_km,
                'nameEn' => $class->name_en,
            ] : null,
        ];
    }

    // Parent: published materials for linked child's active classes.
    public function getForParentChild(User $user, int $studentId)
    {
        if (!$this->parentService->ownsChild($user, $studentId)) {
            abort(403, 'You can only view your linked child.');
        }

        $classIds = DB::table('enrollments')
            ->where('student_id', $studentId)
            ->where('status', 'ACTIVE')
            ->pluck('class_id');

        return LearningMaterial::query()
            ->whereNotNull('published_at')
            ->whereHas(
                'teacherAssignment',
                fn(Builder $q) => $q->whereIn('class_id', $classIds)
            )
            ->with([
                'teacherAssignment.subject:id,code,name_km,name_en',
                'teacherAssignment.teacher:id,teacher_code,first_name_km,last_name_km,first_name_en,last_name_en',
                'teacherAssignment.schoolClass:id,name_km,name_en',
            ])
            ->orderByDesc('published_at')
            ->get();
    }
}

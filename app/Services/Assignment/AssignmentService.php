<?php

namespace App\Services\Assignment;

use App\Models\Assignment;
use App\Models\TeacherAssignment;
use App\Models\User;
use App\Services\Parent\ParentService;
use App\Services\TeacherAssignment\TeacherAssignmentService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AssignmentService
{
    public function __construct(
        private readonly ParentService $parentService,
        private readonly TeacherAssignmentService $teacherAssignmentService
    ) {}

    public function getAll(array $f = [], ?User $user = null): LengthAwarePaginator
    {
        $q = Assignment::query()->with([
            'teacherAssignment.subject',
            'teacherAssignment.schoolClass',
        ]);

        if ($user) $q = $this->applyAccessScope($q, $user);

        $q->when($f['teacher_assignment_id'] ?? null,
            fn(Builder $q, $id) => $q->where('teacher_assignment_id', $id));

        $q->when($f['teacher_id'] ?? null,
            fn(Builder $q, $id) => $q->whereHas('teacherAssignment',
                fn(Builder $x) => $x->where('teacher_id', $id)));

        $q->when($f['class_id'] ?? null,
            fn(Builder $q, $id) => $q->whereHas('teacherAssignment',
                fn(Builder $x) => $x->where('class_id', $id)));

        $q->when($f['subject_id'] ?? null,
            fn(Builder $q, $id) => $q->whereHas('teacherAssignment',
                fn(Builder $x) => $x->where('subject_id', $id)));

        $q->when($f['status'] ?? null,
            fn(Builder $q, $v) => $q->where('status', $v));

        $q->when($f['search'] ?? null,
            fn(Builder $q, $s) => $q->where(fn(Builder $x) =>
                $x->where('title_km', 'ilike', "%{$s}%")
                    ->orWhere('title_en', 'ilike', "%{$s}%")));

        $result = $q->orderByDesc('assigned_at')->paginate($f['per_page'] ?? 15);

        if ($user?->role?->code === 'TEACHER') {
            $result->setCollection(
                $result->getCollection()->map(fn($a) => $this->formatTeacherAssignment($a))
            );
        }

        return $result;
    }

    public function create(array $d, User $user): Assignment
    {
        return DB::transaction(function () use ($d, $user) {
            $this->validateBusinessRules($d);

            $ta = TeacherAssignment::findOrFail($d['teacher_assignment_id']);
            $this->teacherAssignmentService->ensureTeacherOwnsAssignment($user, $ta);

            return Assignment::create($d)->load('teacherAssignment');
        });
    }

    public function update(Assignment $a, array $d, User $user): Assignment
    {
        return DB::transaction(function () use ($a, $d, $user) {
            $a->loadMissing('teacherAssignment');

            $this->teacherAssignmentService
                ->ensureTeacherOwnsAssignment($user, $a->teacherAssignment);

            $m = array_merge([
                'teacher_assignment_id' => $a->teacher_assignment_id,
                'assigned_at' => $a->assigned_at->toISOString(),
                'due_at' => $a->due_at->toISOString(),
                'max_score' => $a->max_score,
            ], $d);

            $this->validateBusinessRules($m);

            if (isset($d['teacher_assignment_id'])) {
                $newTa = TeacherAssignment::findOrFail($d['teacher_assignment_id']);
                $this->teacherAssignmentService->ensureTeacherOwnsAssignment($user, $newTa);
            }

            $a->update($d);
            return $a->refresh()->load('teacherAssignment');
        });
    }

    public function delete(Assignment $a, User $user): void
    {
        DB::transaction(function () use ($a, $user) {
            $a->loadMissing('teacherAssignment');

            $this->teacherAssignmentService
                ->ensureTeacherOwnsAssignment($user, $a->teacherAssignment);

            if ($a->submissions()->exists()) {
                abort(409, 'Cannot delete an assignment that already has submissions.');
            }

            $a->delete();
        });
    }

    public function applyAccessScope(Builder $query, User $user): Builder
    {
        $role = $user->role->code;

        if (in_array($role, ['SUPER_ADMIN', 'ADMIN'], true)) return $query;

        if ($role === 'TEACHER') {
            return $query->whereHas('teacherAssignment.teacher',
                fn(Builder $q) => $q->where('user_id', $user->id));
        }

        return $query->whereRaw('1 = 0');
    }

    private function validateBusinessRules(array $d): void
    {
        if (strtotime($d['due_at']) <= strtotime($d['assigned_at'])) {
            throw ValidationException::withMessages([
                'due_at' => ['The due time must be after the assigned time.'],
            ]);
        }

        if ((float)$d['max_score'] <= 0) {
            throw ValidationException::withMessages([
                'max_score' => ['The maximum score must be greater than zero.'],
            ]);
        }

        $ta = TeacherAssignment::findOrFail($d['teacher_assignment_id']);

        if (isset($ta->status) && strtoupper((string)$ta->status) !== 'ACTIVE') {
            throw ValidationException::withMessages([
                'teacher_assignment_id' => ['The selected teacher assignment is not active.'],
            ]);
        }
    }

    private function formatTeacherAssignment(Assignment $a): array
    {
        $ta = $a->teacherAssignment;
        $subject = $ta?->subject;
        $class = $ta?->schoolClass;

        return [
            'id' => $a->id,
            'titleKm' => $a->title_km,
            'titleEn' => $a->title_en,
            'descriptionKm' => $a->description_km,
            'descriptionEn' => $a->description_en,
            'assignedAt' => $a->assigned_at,
            'dueAt' => $a->due_at,
            'maxScore' => $a->max_score,
            'status' => $a->status,
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

    // Parent: linked child assignments only
    public function getForParentChild(User $user, int $studentId)
    {
        if (!$this->parentService->ownsChild($user, $studentId)) {
            abort(403, 'You can only view your linked child.');
        }

        $classIds = DB::table('enrollments')
            ->where('student_id', $studentId)
            ->where('status', 'ACTIVE')
            ->pluck('class_id');

        return Assignment::query()
            ->where('status', 'PUBLISHED')
            ->whereHas('teacherAssignment',
                fn(Builder $q) => $q->whereIn('class_id', $classIds))
            ->with([
                'teacherAssignment.subject:id,code,name_km,name_en',
                'teacherAssignment.teacher:id,teacher_code,first_name_km,last_name_km,first_name_en,last_name_en',
                'teacherAssignment.schoolClass:id,name_km,name_en',
            ])
            ->orderByDesc('assigned_at')
            ->get()
            ->map(function ($a) {
                $ta = $a->teacherAssignment;
                $subject = $ta?->subject;
                $teacher = $ta?->teacher;
                $class = $ta?->schoolClass;

                return [
                    'id' => $a->id,
                    'titleKm' => $a->title_km,
                    'titleEn' => $a->title_en,
                    'descriptionKm' => $a->description_km,
                    'descriptionEn' => $a->description_en,
                    'assignedAt' => $a->assigned_at,
                    'dueAt' => $a->due_at,
                    'maxScore' => $a->max_score,
                    'status' => $a->status,
                    'subject' => $subject ? [
                        'id' => $subject->id,
                        'code' => $subject->code,
                        'nameKm' => $subject->name_km,
                        'nameEn' => $subject->name_en,
                    ] : null,
                    'teacher' => $teacher ? [
                        'id' => $teacher->id,
                        'teacherCode' => $teacher->teacher_code,
                        'fullNameKm' => trim($teacher->first_name_km.' '.$teacher->last_name_km),
                        'fullNameEn' => trim($teacher->first_name_en.' '.$teacher->last_name_en),
                    ] : null,
                    'class' => $class ? [
                        'id' => $class->id,
                        'nameKm' => $class->name_km,
                        'nameEn' => $class->name_en,
                    ] : null,
                ];
            });
    }
}
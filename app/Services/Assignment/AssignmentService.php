<?php

namespace App\Services\Assignment;

use App\Models\Assignment;
use App\Models\TeacherAssignment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Services\Parent\ParentService;

class AssignmentService
{
    public function __construct(
        private readonly ParentService $parentService
    ) {}
    public function getAll(array $f = []): LengthAwarePaginator
    {
        $q = Assignment::query()->with('teacherAssignment');
        $q->when($f['teacher_assignment_id'] ?? null, fn(Builder $q, $id) => $q->where('teacher_assignment_id', $id));
        $q->when($f['teacher_id'] ?? null, fn(Builder $q, $id) => $q->whereHas('teacherAssignment', fn(Builder $x) => $x->where('teacher_id', $id)));
        $q->when($f['class_id'] ?? null, fn(Builder $q, $id) => $q->whereHas('teacherAssignment', fn(Builder $x) => $x->where('class_id', $id)));
        $q->when($f['subject_id'] ?? null, fn(Builder $q, $id) => $q->whereHas('teacherAssignment', fn(Builder $x) => $x->where('subject_id', $id)));
        $q->when($f['status'] ?? null, fn(Builder $q, $v) => $q->where('status', $v));
        $q->when($f['search'] ?? null, fn(Builder $q, $s) => $q->where(fn(Builder $x) => $x->where('title_km', 'ilike', "%{$s}%")->orWhere('title_en', 'ilike', "%{$s}%")));
        return $q->orderByDesc('assigned_at')->paginate($f['per_page'] ?? 15);
    }
    public function create(array $d): Assignment
    {
        return DB::transaction(function () use ($d) {
            $this->validateBusinessRules($d);
            return Assignment::create($d)->load('teacherAssignment');
        });
    }
    public function update(Assignment $a, array $d): Assignment
    {
        return DB::transaction(function () use ($a, $d) {
            $m = array_merge(['teacher_assignment_id' => $a->teacher_assignment_id, 'assigned_at' => $a->assigned_at->toISOString(), 'due_at' => $a->due_at->toISOString(), 'max_score' => $a->max_score], $d);
            $this->validateBusinessRules($m);
            $a->update($d);
            return $a->refresh()->load('teacherAssignment');
        });
    }
    public function delete(Assignment $a): void
    {
        DB::transaction(function () use ($a) {
            if ($a->submissions()->exists()) abort(409, 'Cannot delete an assignment that already has submissions.');
            $a->delete();
        });
    }
    private function validateBusinessRules(array $d): void
    {
        if (strtotime($d['due_at']) <= strtotime($d['assigned_at'])) throw ValidationException::withMessages(['due_at' => ['The due time must be after the assigned time.']]);
        if ((float)$d['max_score'] <= 0) throw ValidationException::withMessages(['max_score' => ['The maximum score must be greater than zero.']]);
        $ta = TeacherAssignment::findOrFail($d['teacher_assignment_id']);
        if (isset($ta->status) && strtoupper((string)$ta->status) !== 'ACTIVE') throw ValidationException::withMessages(['teacher_assignment_id' => ['The selected teacher assignment is not active.']]);
    }

    // Parent role only 

    // Parent role only
    public function getForParentChild(
        User $user,
        int $studentId
    ) {
        if (!$this->parentService->ownsChild($user, $studentId)) {
            abort(403, 'You can only view your linked child.');
        }

        $classIds = DB::table('enrollments')
            ->where('student_id', $studentId)
            ->where('status', 'ACTIVE')
            ->pluck('class_id');

        return Assignment::query()
            ->where('status', 'PUBLISHED')
            ->whereHas(
                'teacherAssignment',
                fn(Builder $q) =>
                $q->whereIn('class_id', $classIds)
            )
            ->with([
                'teacherAssignment.subject:id,code,name_km,name_en',
                'teacherAssignment.teacher:id,teacher_code,first_name_km,last_name_km,first_name_en,last_name_en',
                'teacherAssignment.schoolClass:id,name_km,name_en',
            ])
            ->orderByDesc('assigned_at')
            ->get()
            ->map(fn($a) => [
                'id' => $a->id,
                'titleKm' => $a->title_km,
                'titleEn' => $a->title_en,
                'descriptionKm' => $a->description_km,
                'descriptionEn' => $a->description_en,
                'assignedAt' => $a->assigned_at,
                'dueAt' => $a->due_at,
                'maxScore' => $a->max_score,
                'status' => $a->status,

                'subject' => $a->teacherAssignment?->subject
                    ? [
                        'id' => $a->teacherAssignment->subject->id,
                        'code' => $a->teacherAssignment->subject->code,
                        'nameKm' => $a->teacherAssignment->subject->name_km,
                        'nameEn' => $a->teacherAssignment->subject->name_en,
                    ]
                    : null,

                'teacher' => $a->teacherAssignment?->teacher
                    ? [
                        'id' => $a->teacherAssignment->teacher->id,
                        'teacherCode' => $a->teacherAssignment->teacher->teacher_code,
                        'fullNameKm' => trim(
                            $a->teacherAssignment->teacher->first_name_km . ' ' .
                                $a->teacherAssignment->teacher->last_name_km
                        ),
                        'fullNameEn' => trim(
                            $a->teacherAssignment->teacher->first_name_en . ' ' .
                                $a->teacherAssignment->teacher->last_name_en
                        ),
                    ]
                    : null,

                'class' => $a->teacherAssignment?->schoolClass
                    ? [
                        'id' => $a->teacherAssignment->schoolClass->id,
                        'nameKm' => $a->teacherAssignment->schoolClass->name_km,
                        'nameEn' => $a->teacherAssignment->schoolClass->name_en,
                    ]
                    : null,
            ]);
    }
}

<?php

namespace App\Services\Assignment;

use App\Models\Assignment;
use App\Models\TeacherAssignment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AssignmentService
{
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
}

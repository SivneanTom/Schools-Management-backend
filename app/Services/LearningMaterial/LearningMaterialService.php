<?php

namespace App\Services\LearningMaterial;

use App\Models\LearningMaterial;
use App\Models\TeacherAssignment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LearningMaterialService
{
    public function getAll(array $f = []): LengthAwarePaginator
    {
        $q = LearningMaterial::query()->with('teacherAssignment');
        $q->when($f['teacher_assignment_id'] ?? null, fn(Builder $q, $id) => $q->where('teacher_assignment_id', $id));
        $q->when($f['teacher_id'] ?? null, fn(Builder $q, $id) => $q->whereHas('teacherAssignment', fn(Builder $x) => $x->where('teacher_id', $id)));
        $q->when($f['class_id'] ?? null, fn(Builder $q, $id) => $q->whereHas('teacherAssignment', fn(Builder $x) => $x->where('class_id', $id)));
        $q->when($f['subject_id'] ?? null, fn(Builder $q, $id) => $q->whereHas('teacherAssignment', fn(Builder $x) => $x->where('subject_id', $id)));
        $q->when($f['material_type'] ?? null, fn(Builder $q, $v) => $q->where('material_type', $v));
        $q->when(array_key_exists('published', $f), function (Builder $q) use ($f) {
            $f['published'] ? $q->whereNotNull('published_at') : $q->whereNull('published_at');
        });
        return $q->orderByDesc('published_at')->orderByDesc('id')->paginate($f['per_page'] ?? 15);
    }
    public function create(array $d): LearningMaterial
    {
        return DB::transaction(function () use ($d) {
            $this->validateBusinessRules($d);
            return LearningMaterial::create($d)->load('teacherAssignment');
        });
    }
    public function update(LearningMaterial $m, array $d): LearningMaterial
    {
        return DB::transaction(function () use ($m, $d) {
            $this->validateBusinessRules(array_merge(['teacher_assignment_id' => $m->teacher_assignment_id], $d));
            $m->update($d);
            return $m->refresh()->load('teacherAssignment');
        });
    }
    public function publish(LearningMaterial $m): LearningMaterial
    {
        $m->update(['published_at' => now()]);
        return $m->refresh()->load('teacherAssignment');
    }
    public function unpublish(LearningMaterial $m): LearningMaterial
    {
        $m->update(['published_at' => null]);
        return $m->refresh()->load('teacherAssignment');
    }
    public function delete(LearningMaterial $m): void
    {
        DB::transaction(fn() => $m->delete());
    }
    private function validateBusinessRules(array $d): void
    {
        $ta = TeacherAssignment::findOrFail($d['teacher_assignment_id']);
        if (isset($ta->status) && strtoupper((string)$ta->status) !== 'ACTIVE') throw ValidationException::withMessages(['teacher_assignment_id' => ['The selected teacher assignment is not active.']]);
    }
}

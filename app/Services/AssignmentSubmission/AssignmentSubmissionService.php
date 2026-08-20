<?php

namespace App\Services\AssignmentSubmission;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AssignmentSubmissionService
{
    public function getAll(array $f = []): LengthAwarePaginator
    {
        $q = AssignmentSubmission::query()->with(['assignment', 'student']);
        $q->when($f['assignment_id'] ?? null, fn(Builder $q, $id) => $q->where('assignment_id', $id));
        $q->when($f['student_id'] ?? null, fn(Builder $q, $id) => $q->where('student_id', $id));
        $q->when($f['status'] ?? null, fn(Builder $q, $v) => $q->where('status', $v));
        return $q->orderByDesc('submitted_at')->orderByDesc('id')->paginate($f['per_page'] ?? 15);
    }
    public function create(array $d): AssignmentSubmission
    {
        return DB::transaction(function () use ($d) {
            $this->validateBusinessRules($d);
            $a = Assignment::findOrFail($d['assignment_id']);
            $submitted = $d['submitted_at'] ?? now();
            $d['submitted_at'] = $submitted;
            if (!isset($d['status'])) $d['status'] = strtotime((string)$submitted) > $a->due_at->timestamp ? 'LATE' : 'SUBMITTED';
            return AssignmentSubmission::create($d)->load(['assignment', 'student']);
        });
    }
    public function update(AssignmentSubmission $s, array $d): AssignmentSubmission
    {
        return DB::transaction(function () use ($s, $d) {
            $m = array_merge(['assignment_id' => $s->assignment_id, 'student_id' => $s->student_id, 'score' => $s->score], $d);
            $this->validateBusinessRules($m, $s->id);
            $s->update($d);
            return $s->refresh()->load(['assignment', 'student']);
        });
    }
    public function delete(AssignmentSubmission $s): void
    {
        DB::transaction(fn() => $s->delete());
    }
    private function validateBusinessRules(array $d, ?int $ignore = null): void
    {
        $a = Assignment::with('teacherAssignment')->findOrFail($d['assignment_id']);
        if (in_array(strtoupper((string)$a->status), ['DRAFT', 'CANCELLED'], true)) throw ValidationException::withMessages(['assignment_id' => ['Submissions are not allowed for a draft or cancelled assignment.']]);
        if (!$a->teacherAssignment) throw ValidationException::withMessages(['assignment_id' => ['The assignment does not have a valid teacher assignment.']]);
        $ok = DB::table('enrollments')->where('student_id', $d['student_id'])->where('class_id', $a->teacherAssignment->class_id)->exists();
        if (!$ok) throw ValidationException::withMessages(['student_id' => ['The selected student is not enrolled in the class for this assignment.']]);
        if (array_key_exists('score', $d) && $d['score'] !== null && (float)$d['score'] > (float)$a->max_score) throw ValidationException::withMessages(['score' => ['The score cannot be greater than the assignment maximum score.']]);
        $dup = AssignmentSubmission::query()->where('assignment_id', $d['assignment_id'])->where('student_id', $d['student_id'])->when($ignore, fn(Builder $q) => $q->where('id', '!=', $ignore))->exists();
        if ($dup) throw ValidationException::withMessages(['student_id' => ['This student already has a submission for this assignment.']]);
    }
}

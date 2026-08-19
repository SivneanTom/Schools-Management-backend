<?php

namespace App\Services\ExamSubject;

use App\Models\Exam;
use App\Models\ExamSubject;
use App\Models\TeacherAssignment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExamSubjectService
{
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = ExamSubject::query()->with(['exam', 'teacherAssignment']);

        $query->when($filters['exam_id'] ?? null, fn (Builder $q, $id) => $q->where('exam_id', $id));
        $query->when($filters['teacher_assignment_id'] ?? null, fn (Builder $q, $id) => $q->where('teacher_assignment_id', $id));
        $query->when($filters['teacher_id'] ?? null, fn (Builder $q, $id) => $q->whereHas('teacherAssignment', fn (Builder $x) => $x->where('teacher_id', $id)));
        $query->when($filters['class_id'] ?? null, fn (Builder $q, $id) => $q->whereHas('teacherAssignment', fn (Builder $x) => $x->where('class_id', $id)));
        $query->when($filters['subject_id'] ?? null, fn (Builder $q, $id) => $q->whereHas('teacherAssignment', fn (Builder $x) => $x->where('subject_id', $id)));
        $query->when($filters['exam_date'] ?? null, fn (Builder $q, $date) => $q->whereDate('exam_date', $date));

        return $query->orderBy('exam_date')->orderBy('start_time')->paginate($filters['per_page'] ?? 15);
    }

    public function create(array $data): ExamSubject
    {
        return DB::transaction(function () use ($data) {
            $this->validateBusinessRules($data);
            return ExamSubject::create($data)->load(['exam', 'teacherAssignment']);
        });
    }

    public function update(ExamSubject $examSubject, array $data): ExamSubject
    {
        return DB::transaction(function () use ($examSubject, $data) {
            $merged = array_merge([
                'exam_id' => $examSubject->exam_id,
                'teacher_assignment_id' => $examSubject->teacher_assignment_id,
                'exam_date' => $examSubject->exam_date->format('Y-m-d'),
                'start_time' => substr((string) $examSubject->start_time, 0, 5),
                'end_time' => substr((string) $examSubject->end_time, 0, 5),
                'max_score' => $examSubject->max_score,
                'pass_score' => $examSubject->pass_score,
            ], $data);

            $this->validateBusinessRules($merged, $examSubject->id);

            $examSubject->update($data);

            return $examSubject->refresh()->load(['exam', 'teacherAssignment']);
        });
    }

    public function delete(ExamSubject $examSubject): void
    {
        DB::transaction(fn () => $examSubject->delete());
    }

    private function validateBusinessRules(array $data, ?int $ignoreId = null): void
    {
        if ($data['start_time'] >= $data['end_time']) {
            throw ValidationException::withMessages([
                'end_time' => ['The end time must be after the start time.'],
            ]);
        }

        if ((float) $data['max_score'] <= 0) {
            throw ValidationException::withMessages([
                'max_score' => ['The maximum score must be greater than zero.'],
            ]);
        }

        if ((float) $data['pass_score'] < 0 || (float) $data['pass_score'] > (float) $data['max_score']) {
            throw ValidationException::withMessages([
                'pass_score' => ['The pass score must be between 0 and the maximum score.'],
            ]);
        }

        $exam = Exam::findOrFail($data['exam_id']);

        $examDate = $data['exam_date'];
        $startDate = $exam->start_date->format('Y-m-d');
        $endDate = $exam->end_date->format('Y-m-d');

        if ($examDate < $startDate || $examDate > $endDate) {
            throw ValidationException::withMessages([
                'exam_date' => ['The exam subject date must be within the exam start and end dates.'],
            ]);
        }

        if (in_array(strtoupper((string) $exam->status), ['COMPLETED', 'CANCELLED'], true)) {
            throw ValidationException::withMessages([
                'exam_id' => ['Subjects cannot be added or changed for a completed or cancelled exam.'],
            ]);
        }

        $assignment = TeacherAssignment::findOrFail($data['teacher_assignment_id']);

        if (isset($assignment->status) && strtoupper((string) $assignment->status) !== 'ACTIVE') {
            throw ValidationException::withMessages([
                'teacher_assignment_id' => ['The selected teacher assignment is not active.'],
            ]);
        }

        if ((int) $assignment->semester_id !== (int) $exam->semester_id) {
            throw ValidationException::withMessages([
                'teacher_assignment_id' => ['The teacher assignment must belong to the same semester as the exam.'],
            ]);
        }

        $conflict = ExamSubject::query()
            ->where('teacher_assignment_id', $data['teacher_assignment_id'])
            ->whereDate('exam_date', $data['exam_date'])
            ->where('start_time', '<', $data['end_time'])
            ->where('end_time', '>', $data['start_time'])
            ->when($ignoreId, fn (Builder $q) => $q->where('id', '!=', $ignoreId))
            ->exists();

        if ($conflict) {
            throw ValidationException::withMessages([
                'start_time' => ['This teacher assignment already has an overlapping exam subject.'],
            ]);
        }
    }
}

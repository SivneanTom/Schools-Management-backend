<?php

namespace App\Services\ExamResult;

use App\Models\ExamResult;
use App\Models\ExamSubject;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExamResultService
{
    public function getAll(
        array $filters = []
    ): LengthAwarePaginator {
        $query = ExamResult::query()
            ->with([
                'examSubject',
                'student',
            ]);

        $query->when(
            $filters['exam_subject_id'] ?? null,
            fn (Builder $query, $id) =>
                $query->where('exam_subject_id', $id)
        );

        $query->when(
            $filters['student_id'] ?? null,
            fn (Builder $query, $id) =>
                $query->where('student_id', $id)
        );

        $query->when(
            $filters['exam_id'] ?? null,
            fn (Builder $query, $id) =>
                $query->whereHas(
                    'examSubject',
                    fn (Builder $q) =>
                        $q->where('exam_id', $id)
                )
        );

        $query->when(
            $filters['grade'] ?? null,
            fn (Builder $query, string $grade) =>
                $query->where('grade', $grade)
        );

        $query->when(
            array_key_exists('published', $filters),
            function (Builder $query) use ($filters): void {
                if ($filters['published']) {
                    $query->whereNotNull('published_at');
                } else {
                    $query->whereNull('published_at');
                }
            }
        );

        return $query
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate($filters['per_page'] ?? 15);
    }

    public function create(array $data): ExamResult
    {
        return DB::transaction(function () use ($data): ExamResult {
            $this->validateBusinessRules($data);

            $result = ExamResult::create($data);

            return $result->load([
                'examSubject',
                'student',
            ]);
        });
    }

    public function update(
        ExamResult $examResult,
        array $data
    ): ExamResult {
        return DB::transaction(
            function () use ($examResult, $data): ExamResult {
                $merged = array_merge(
                    [
                        'exam_subject_id' =>
                            $examResult->exam_subject_id,
                        'student_id' =>
                            $examResult->student_id,
                        'score' =>
                            $examResult->score,
                        'grade' =>
                            $examResult->grade,
                        'remarks_km' =>
                            $examResult->remarks_km,
                        'remarks_en' =>
                            $examResult->remarks_en,
                        'published_at' =>
                            $examResult->published_at,
                    ],
                    $data
                );

                $this->validateBusinessRules(
                    $merged,
                    $examResult->id
                );

                $examResult->update($data);

                return $examResult
                    ->refresh()
                    ->load([
                        'examSubject',
                        'student',
                    ]);
            }
        );
    }

    public function publish(
        ExamResult $examResult
    ): ExamResult {
        return DB::transaction(
            function () use ($examResult): ExamResult {
                $examResult->update([
                    'published_at' => now(),
                ]);

                return $examResult
                    ->refresh()
                    ->load([
                        'examSubject',
                        'student',
                    ]);
            }
        );
    }

    public function unpublish(
        ExamResult $examResult
    ): ExamResult {
        return DB::transaction(
            function () use ($examResult): ExamResult {
                $examResult->update([
                    'published_at' => null,
                ]);

                return $examResult
                    ->refresh()
                    ->load([
                        'examSubject',
                        'student',
                    ]);
            }
        );
    }

    public function delete(
        ExamResult $examResult
    ): void {
        DB::transaction(
            fn () => $examResult->delete()
        );
    }

    private function validateBusinessRules(
        array $data,
        ?int $ignoreResultId = null
    ): void {
        $examSubject = ExamSubject::query()
            ->with('teacherAssignment')
            ->findOrFail(
                $data['exam_subject_id']
            );

        if (
            (float) $data['score'] >
            (float) $examSubject->max_score
        ) {
            throw ValidationException::withMessages([
                'score' => [
                    'The score cannot be greater than the maximum score for this exam subject.',
                ],
            ]);
        }

        /*
         * The student must be enrolled in the class that belongs
         * to the teacher assignment of this exam subject.
         */
        if (!$examSubject->teacherAssignment) {
            throw ValidationException::withMessages([
                'exam_subject_id' => [
                    'The exam subject does not have a valid teacher assignment.',
                ],
            ]);
        }

        $studentIsInClass = DB::table('enrollments')
            ->where(
                'student_id',
                $data['student_id']
            )
            ->where(
                'class_id',
                $examSubject
                    ->teacherAssignment
                    ->class_id
            )
            ->exists();

        if (!$studentIsInClass) {
            throw ValidationException::withMessages([
                'student_id' => [
                    'The selected student is not enrolled in the class for this exam subject.',
                ],
            ]);
        }

        /*
         * Friendly application-level duplicate protection.
         * Database UNIQUE is the final protection.
         */
        $duplicate = ExamResult::query()
            ->where(
                'exam_subject_id',
                $data['exam_subject_id']
            )
            ->where(
                'student_id',
                $data['student_id']
            )
            ->when(
                $ignoreResultId,
                fn (Builder $query) =>
                    $query->where(
                        'id',
                        '!=',
                        $ignoreResultId
                    )
            )
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages([
                'student_id' => [
                    'A result already exists for this student in this exam subject.',
                ],
            ]);
        }
    }
}

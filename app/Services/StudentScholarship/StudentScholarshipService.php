<?php

namespace App\Services\StudentScholarship;

use App\Models\Scholarship;
use App\Models\StudentScholarship;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StudentScholarshipService
{
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $query = StudentScholarship::query()
            ->with(['student', 'scholarship', 'academicYear']);

        if (!empty($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        if (!empty($filters['scholarship_id'])) {
            $query->where('scholarship_id', $filters['scholarship_id']);
        }

        if (!empty($filters['academic_year_id'])) {
            $query->where('academic_year_id', $filters['academic_year_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', strtoupper($filters['status']));
        }

        return $query
            ->latest('id')
            ->paginate((int) ($filters['per_page'] ?? 20));
    }

    public function create(array $data): StudentScholarship
    {
        return DB::transaction(function () use ($data) {
            $scholarship = Scholarship::findOrFail($data['scholarship_id']);

            if (!$scholarship->is_active) {
                throw ValidationException::withMessages([
                    'scholarship_id' => 'The selected scholarship is inactive.',
                ]);
            }

            $awardedAt = Carbon::parse($data['awarded_at'] ?? now()->toDateString());

            $this->validateAwardDate($scholarship, $awardedAt);
            $this->ensureUniqueAssignment(
                $data['student_id'],
                $data['scholarship_id'],
                $data['academic_year_id']
            );

            $data['awarded_at'] = $awardedAt->toDateString();
            $data['status'] = $data['status'] ?? StudentScholarship::STATUS_ACTIVE;

            $studentScholarship = StudentScholarship::create($data);

            return $studentScholarship->load([
                'student',
                'scholarship',
                'academicYear',
            ]);
        });
    }

    public function update(StudentScholarship $studentScholarship, array $data): StudentScholarship
    {
        return DB::transaction(function () use ($studentScholarship, $data) {
            $studentId = $data['student_id'] ?? $studentScholarship->student_id;
            $scholarshipId = $data['scholarship_id'] ?? $studentScholarship->scholarship_id;
            $academicYearId = $data['academic_year_id'] ?? $studentScholarship->academic_year_id;

            $scholarship = Scholarship::findOrFail($scholarshipId);

            if (
                isset($data['scholarship_id'])
                && !$scholarship->is_active
            ) {
                throw ValidationException::withMessages([
                    'scholarship_id' => 'The selected scholarship is inactive.',
                ]);
            }

            $awardedAt = Carbon::parse(
                $data['awarded_at']
                ?? $studentScholarship->awarded_at->format('Y-m-d')
            );

            $this->validateAwardDate($scholarship, $awardedAt);

            $this->ensureUniqueAssignment(
                $studentId,
                $scholarshipId,
                $academicYearId,
                $studentScholarship->id
            );

            $studentScholarship->update($data);

            return $studentScholarship
                ->refresh()
                ->load(['student', 'scholarship', 'academicYear']);
        });
    }

    public function delete(StudentScholarship $studentScholarship): void
    {
        if ($studentScholarship->status === StudentScholarship::STATUS_ACTIVE) {
            throw ValidationException::withMessages([
                'student_scholarship' => 'An active scholarship should be cancelled before deleting it.',
            ]);
        }

        $studentScholarship->delete();
    }

    private function validateAwardDate(Scholarship $scholarship, Carbon $awardedAt): void
    {
        if ($scholarship->start_date && $awardedAt->lt($scholarship->start_date)) {
            throw ValidationException::withMessages([
                'awarded_at' => 'The award date cannot be before the scholarship start date.',
            ]);
        }

        if ($scholarship->end_date && $awardedAt->gt($scholarship->end_date)) {
            throw ValidationException::withMessages([
                'awarded_at' => 'The award date cannot be after the scholarship end date.',
            ]);
        }
    }

    private function ensureUniqueAssignment(
        int $studentId,
        int $scholarshipId,
        int $academicYearId,
        ?int $ignoreId = null
    ): void {
        $query = StudentScholarship::query()
            ->where('student_id', $studentId)
            ->where('scholarship_id', $scholarshipId)
            ->where('academic_year_id', $academicYearId);

        if ($ignoreId !== null) {
            $query->whereKeyNot($ignoreId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'student_scholarship' => 'This scholarship is already assigned to this student for the selected academic year.',
            ]);
        }
    }
}

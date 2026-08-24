<?php

namespace App\Services\StudentFee;

use App\Models\FeeType;
use App\Models\StudentFee;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StudentFeeService
{
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $query = StudentFee::query()
            ->with(['student', 'feeType', 'academicYear']);

        if (!empty($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        if (!empty($filters['fee_type_id'])) {
            $query->where('fee_type_id', $filters['fee_type_id']);
        }

        if (!empty($filters['academic_year_id'])) {
            $query->where('academic_year_id', $filters['academic_year_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', strtoupper($filters['status']));
        }

        if (!empty($filters['due_from'])) {
            $query->whereDate('due_date', '>=', $filters['due_from']);
        }

        if (!empty($filters['due_to'])) {
            $query->whereDate('due_date', '<=', $filters['due_to']);
        }

        return $query
            ->latest('id')
            ->paginate((int) ($filters['per_page'] ?? 20));
    }

    public function create(array $data): StudentFee
    {
        return DB::transaction(function () use ($data) {
            $feeType = FeeType::findOrFail($data['fee_type_id']);

            if (!$feeType->is_active) {
                throw ValidationException::withMessages([
                    'fee_type_id' => 'The selected fee type is inactive.',
                ]);
            }

            $this->ensureUniqueAssignment(
                $data['student_id'],
                $data['fee_type_id'],
                $data['academic_year_id']
            );

            if (!array_key_exists('amount', $data) || $data['amount'] === null) {
                $data['amount'] = $feeType->default_amount;
            }

            $data['status'] = $data['status'] ?? StudentFee::STATUS_UNPAID;

            $studentFee = StudentFee::create($data);

            return $studentFee->load(['student', 'feeType', 'academicYear']);
        });
    }

    public function update(
        StudentFee $studentFee,
        array $data
    ): StudentFee {
        return DB::transaction(function () use (
            $studentFee,
            $data
        ) {
            $studentId =
                $data['student_id']
                ?? $studentFee->student_id;

            $feeTypeId =
                $data['fee_type_id']
                ?? $studentFee->fee_type_id;

            $academicYearId =
                $data['academic_year_id']
                ?? $studentFee->academic_year_id;

            if (isset($data['fee_type_id'])) {
                $feeType = FeeType::findOrFail(
                    $feeTypeId
                );

                if (!$feeType->is_active) {
                    throw ValidationException::withMessages([
                        'fee_type_id' =>
                        'The selected fee type is inactive.',
                    ]);
                }

                /*
             * If fee type changes and amount
             * was not manually supplied,
             * use the new fee type default amount.
             */
                if (!array_key_exists('amount', $data)) {
                    $data['amount'] =
                        $feeType->default_amount;
                }
            }

            $this->ensureUniqueAssignment(
                $studentId,
                $feeTypeId,
                $academicYearId,
                $studentFee->id
            );

            $studentFee->update($data);

            return $studentFee
                ->refresh()
                ->load([
                    'student',
                    'feeType',
                    'academicYear',
                ]);
        });
    }

    public function delete(StudentFee $studentFee): void
    {
        if (in_array($studentFee->status, [
            StudentFee::STATUS_PARTIALLY_PAID,
            StudentFee::STATUS_PAID,
        ], true)) {
            throw ValidationException::withMessages([
                'student_fee' => 'A paid or partially paid student fee should not be deleted. Cancel it instead.',
            ]);
        }

        $studentFee->delete();
    }

    private function ensureUniqueAssignment(
        int $studentId,
        int $feeTypeId,
        int $academicYearId,
        ?int $ignoreId = null
    ): void {
        $query = StudentFee::query()
            ->where('student_id', $studentId)
            ->where('fee_type_id', $feeTypeId)
            ->where('academic_year_id', $academicYearId);

        if ($ignoreId !== null) {
            $query->whereKeyNot($ignoreId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'student_fee' => 'This fee type is already assigned to this student for the selected academic year.',
            ]);
        }
    }
}

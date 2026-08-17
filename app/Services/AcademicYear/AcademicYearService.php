<?php

namespace App\Services\AcademicYear;

use App\Models\AcademicYear;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AcademicYearService
{
    public function create(array $data): AcademicYear
    {
        return DB::transaction(function () use ($data) {

            $this->validateDateRange(
                $data['startDate'],
                $data['endDate']
            );

            $this->validateNoOverlap(
                $data['startDate'],
                $data['endDate']
            );

            $status = $data['status'] ?? 'INACTIVE';

            if ($status === 'ACTIVE') {
                AcademicYear::where('status', 'ACTIVE')
                    ->update([
                        'status' => 'INACTIVE',
                    ]);
            }

            return AcademicYear::create([
                'name' => $data['name'],
                'start_date' => $data['startDate'],
                'end_date' => $data['endDate'],
                'status' => $status,
            ]);
        });
    }

    public function update(
        AcademicYear $academicYear,
        array $data
    ): AcademicYear {
        return DB::transaction(function () use (
            $academicYear,
            $data
        ) {

            $startDate =
                $data['startDate']
                ?? $academicYear->start_date->format('Y-m-d');

            $endDate =
                $data['endDate']
                ?? $academicYear->end_date->format('Y-m-d');

            $this->validateDateRange(
                $startDate,
                $endDate
            );

            $this->validateNoOverlap(
                $startDate,
                $endDate,
                $academicYear->id
            );

            if (array_key_exists('name', $data)) {
                $academicYear->name = $data['name'];
            }

            if (array_key_exists('startDate', $data)) {
                $academicYear->start_date =
                    $data['startDate'];
            }

            if (array_key_exists('endDate', $data)) {
                $academicYear->end_date =
                    $data['endDate'];
            }

            $academicYear->save();

            return $academicYear;
        });
    }

    public function updateStatus(
        AcademicYear $academicYear,
        string $status
    ): AcademicYear {
        return DB::transaction(function () use (
            $academicYear,
            $status
        ) {

            if ($status === 'ACTIVE') {
                AcademicYear::where('id', '!=', $academicYear->id)
                    ->where('status', 'ACTIVE')
                    ->update([
                        'status' => 'INACTIVE',
                    ]);
            }

            $academicYear->update([
                'status' => $status,
            ]);

            return $academicYear;
        });
    }

    private function validateDateRange(
        string $startDate,
        string $endDate
    ): void {
        if (
            strtotime($endDate)
            <= strtotime($startDate)
        ) {
            throw ValidationException::withMessages([
                'endDate' =>
                    'End date must be after start date.',
            ]);
        }
    }

    private function validateNoOverlap(
        string $startDate,
        string $endDate,
        ?int $ignoreId = null
    ): void {
        $query = AcademicYear::query()
            ->where('start_date', '<=', $endDate)
            ->where('end_date', '>=', $startDate);

        if ($ignoreId !== null) {
            $query->where('id', '!=', $ignoreId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'startDate' =>
                    'Academic year overlaps with an existing academic year.',
            ]);
        }
    }
}
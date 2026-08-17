<?php

namespace App\Services\Semester;

use App\Models\AcademicYear;
use App\Models\Semester;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SemesterService
{
    public function create(array $data): Semester
    {
        return DB::transaction(function () use ($data) {

            $academicYear = AcademicYear::findOrFail(
                $data['academicYearId']
            );

            $this->validateDateRange(
                $data['startDate'],
                $data['endDate']
            );

            $this->validateInsideAcademicYear(
                $academicYear,
                $data['startDate'],
                $data['endDate']
            );

            $this->validateNoOverlap(
                $academicYear->id,
                $data['startDate'],
                $data['endDate']
            );

            $status = $data['status'] ?? 'INACTIVE';

            if ($status === 'ACTIVE') {
                Semester::where(
                    'academic_year_id',
                    $academicYear->id
                )
                    ->where('status', 'ACTIVE')
                    ->update([
                        'status' => 'INACTIVE',
                    ]);
            }

            $semester = Semester::create([
                'academic_year_id' => $academicYear->id,
                'name' => $data['name'],
                'start_date' => $data['startDate'],
                'end_date' => $data['endDate'],
                'status' => $status,
            ]);

            return $semester->load('academicYear');
        });
    }

    public function update(
        Semester $semester,
        array $data
    ): Semester {
        return DB::transaction(function () use (
            $semester,
            $data
        ) {

            $academicYearId =
                $data['academicYearId']
                ?? $semester->academic_year_id;

            $academicYear = AcademicYear::findOrFail(
                $academicYearId
            );

            $startDate =
                $data['startDate']
                ?? $semester->start_date->format('Y-m-d');

            $endDate =
                $data['endDate']
                ?? $semester->end_date->format('Y-m-d');

            $this->validateDateRange(
                $startDate,
                $endDate
            );

            $this->validateInsideAcademicYear(
                $academicYear,
                $startDate,
                $endDate
            );

            $this->validateNoOverlap(
                $academicYear->id,
                $startDate,
                $endDate,
                $semester->id
            );

            if (array_key_exists('academicYearId', $data)) {
                $semester->academic_year_id =
                    $data['academicYearId'];
            }

            if (array_key_exists('name', $data)) {
                $semester->name = $data['name'];
            }

            if (array_key_exists('startDate', $data)) {
                $semester->start_date =
                    $data['startDate'];
            }

            if (array_key_exists('endDate', $data)) {
                $semester->end_date =
                    $data['endDate'];
            }

            $semester->save();

            return $semester->load('academicYear');
        });
    }

    public function updateStatus(
        Semester $semester,
        string $status
    ): Semester {
        return DB::transaction(function () use (
            $semester,
            $status
        ) {

            if ($status === 'ACTIVE') {
                Semester::where(
                    'academic_year_id',
                    $semester->academic_year_id
                )
                    ->where('id', '!=', $semester->id)
                    ->where('status', 'ACTIVE')
                    ->update([
                        'status' => 'INACTIVE',
                    ]);
            }

            $semester->update([
                'status' => $status,
            ]);

            return $semester;
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

    private function validateInsideAcademicYear(
        AcademicYear $academicYear,
        string $startDate,
        string $endDate
    ): void {
        $yearStart =
            $academicYear->start_date->format('Y-m-d');

        $yearEnd =
            $academicYear->end_date->format('Y-m-d');

        if (
            $startDate < $yearStart
            || $endDate > $yearEnd
        ) {
            throw ValidationException::withMessages([
                'startDate' =>
                    'Semester dates must be inside the academic year date range.',
            ]);
        }
    }

    private function validateNoOverlap(
        int $academicYearId,
        string $startDate,
        string $endDate,
        ?int $ignoreId = null
    ): void {
        $query = Semester::query()
            ->where(
                'academic_year_id',
                $academicYearId
            )
            ->where(
                'start_date',
                '<=',
                $endDate
            )
            ->where(
                'end_date',
                '>=',
                $startDate
            );

        if ($ignoreId !== null) {
            $query->where(
                'id',
                '!=',
                $ignoreId
            );
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'startDate' =>
                    'Semester dates overlap with another semester in this academic year.',
            ]);
        }
    }
}
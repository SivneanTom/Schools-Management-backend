<?php

namespace App\Services\Subject;

use App\Models\Subject;
use Illuminate\Support\Facades\DB;

class SubjectService
{
    public function create(array $data): Subject
    {
        return DB::transaction(function () use ($data) {
            return Subject::create([
                'code' => strtoupper(trim($data['code'])),
                'name_km' => trim($data['nameKm']),
                'name_en' => trim($data['nameEn']),
                'description_km' => isset($data['descriptionKm'])
                    ? trim($data['descriptionKm'])
                    : null,
                'description_en' => isset($data['descriptionEn'])
                    ? trim($data['descriptionEn'])
                    : null,
                'credit_hours' => $data['creditHours'],
                'is_active' => $data['isActive'] ?? true,
            ]);
        });
    }

    public function update(
        Subject $subject,
        array $data
    ): Subject {
        return DB::transaction(function () use (
            $subject,
            $data
        ) {
            if (array_key_exists('code', $data)) {
                $subject->code = strtoupper(
                    trim($data['code'])
                );
            }

            if (array_key_exists('nameKm', $data)) {
                $subject->name_km = trim($data['nameKm']);
            }

            if (array_key_exists('nameEn', $data)) {
                $subject->name_en = trim($data['nameEn']);
            }

            if (array_key_exists('descriptionKm', $data)) {
                $subject->description_km = $data['descriptionKm'] === null
                    ? null
                    : trim($data['descriptionKm']);
            }

            if (array_key_exists('descriptionEn', $data)) {
                $subject->description_en = $data['descriptionEn'] === null
                    ? null
                    : trim($data['descriptionEn']);
            }

            if (array_key_exists('creditHours', $data)) {
                $subject->credit_hours = $data['creditHours'];
            }

            $subject->save();

            return $subject->refresh();
        });
    }

    public function updateStatus(
        Subject $subject,
        bool $isActive
    ): Subject {
        return DB::transaction(function () use (
            $subject,
            $isActive
        ) {
            $subject->update([
                'is_active' => $isActive,
            ]);

            return $subject->refresh();
        });
    }
}

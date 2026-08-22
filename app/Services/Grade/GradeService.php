<?php

namespace App\Services\Grade;

use App\Models\Grade;
use Illuminate\Support\Facades\DB;

class GradeService
{
    public function create(array $data): Grade
    {
        return DB::transaction(function () use ($data) {

            return Grade::create([
                'code' => strtoupper(
                    trim($data['code'])
                ),

                'name_km' =>
                $data['nameKm'],

                'name_en' =>
                $data['nameEn'],

                'order_no' =>
                $data['orderNo'] ?? null,

                'status' =>
                $data['status'] ?? 'ACTIVE',
            ]);
        });
    }

    public function update(
        Grade $grade,
        array $data
    ): Grade {
        return DB::transaction(function () use (
            $grade,
            $data
        ) {

            if (array_key_exists('code', $data)) {
                $grade->code = strtoupper(
                    trim($data['code'])
                );
            }

            if (array_key_exists('nameKm', $data)) {
                $grade->name_km =
                    $data['nameKm'];
            }

            if (array_key_exists('nameEn', $data)) {
                $grade->name_en =
                    $data['nameEn'];
            }

            if (array_key_exists('orderNo', $data)) {
                $grade->order_no =
                    $data['orderNo'];
            }

            $grade->save();

            return $grade;
        });
    }

    public function updateStatus(
        Grade $grade,
        string $status
    ): Grade {
        return DB::transaction(function () use (
            $grade,
            $status
        ) {

            $grade->update([
                'status' => $status,
            ]);

            return $grade;
        });
    }

    public function delete(Grade $grade): Grade
    {
        return DB::transaction(function () use ($grade) {

            $grade->update([
                'status' => 'INACTIVE'
            ]);

            return $grade;
        });
    }
}

<?php

namespace App\Services\Scholarship;

use App\Models\Scholarship;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ScholarshipService
{
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $query = Scholarship::query();

        if (!empty($filters['search'])) {
            $search = mb_strtolower(trim($filters['search']));
            $term = "%{$search}%";

            $query->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(name_km) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(name_en) LIKE ?', [$term]);
            });
        }

        if (!empty($filters['discount_type'])) {
            $query->where('discount_type', strtoupper($filters['discount_type']));
        }

        if (array_key_exists('is_active', $filters) && $filters['is_active'] !== null) {
            $query->where(
                'is_active',
                filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN)
            );
        }

        return $query
            ->latest('id')
            ->paginate((int) ($filters['per_page'] ?? 20));
    }

    public function create(array $data): Scholarship
    {
        return DB::transaction(fn () => Scholarship::create($data));
    }

    public function update(Scholarship $scholarship, array $data): Scholarship
    {
        return DB::transaction(function () use ($scholarship, $data) {
            $scholarship->update($data);

            return $scholarship->refresh();
        });
    }

    public function delete(Scholarship $scholarship): void
    {
        if ($scholarship->studentScholarships()->exists()) {
            throw ValidationException::withMessages([
                'scholarship' => 'This scholarship has already been awarded to students. Deactivate it instead of deleting it.',
            ]);
        }

        $scholarship->delete();
    }
}

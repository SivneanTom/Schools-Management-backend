<?php

namespace App\Services\FeeType;

use App\Models\FeeType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FeeTypeService
{
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $query = FeeType::query();

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);

            $query->where(function ($q) use ($search) {
                $term = '%' . mb_strtolower($search) . '%';

                $q->whereRaw('LOWER(code) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(name_km) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(name_en) LIKE ?', [$term]);
            });
        }

        if (array_key_exists('is_active', $filters) && $filters['is_active'] !== null) {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        if (!empty($filters['frequency'])) {
            $query->where('frequency', strtoupper($filters['frequency']));
        }

        return $query
            ->orderBy('id', 'asc')
            ->paginate((int) ($filters['per_page'] ?? 20));
    }

    public function create(array $data): FeeType
    {
        return DB::transaction(function () use ($data) {
            return FeeType::create($data);
        });
    }

    public function update(FeeType $feeType, array $data): FeeType
    {
        return DB::transaction(function () use ($feeType, $data) {
            $feeType->update($data);

            return $feeType->refresh();
        });
    }

    public function delete(FeeType $feeType): void
    {
        if ($feeType->studentFees()->exists()) {
            throw ValidationException::withMessages([
                'fee_type' => 'This fee type is already used by student fees. Deactivate it instead of deleting it.',
            ]);
        }

        $feeType->delete();
    }
}

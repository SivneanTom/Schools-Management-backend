<?php

namespace App\Services\PaymentMethod;

use App\Models\PaymentMethod;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentMethodService
{
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $query = PaymentMethod::query();

        if (!empty($filters['search'])) {
            $search = strtolower(
                trim($filters['search'])
            );

            $query->where(function ($q) use ($search) {
                $q->whereRaw(
                    'LOWER(code) LIKE ?',
                    ["%{$search}%"]
                )
                    ->orWhereRaw(
                        'LOWER(name_km) LIKE ?',
                        ["%{$search}%"]
                    )
                    ->orWhereRaw(
                        'LOWER(name_en) LIKE ?',
                        ["%{$search}%"]
                    );
            });
        }

        if (isset($filters['is_active'])) {
            $query->where(
                'is_active',
                filter_var(
                    $filters['is_active'],
                    FILTER_VALIDATE_BOOLEAN
                )
            );
        }

        return $query
            ->orderBy('id', 'asc')
            ->paginate(
                $filters['per_page'] ?? 20
            );
    }

    public function create(array $data): PaymentMethod
    {
        return DB::transaction(function () use ($data) {
            return PaymentMethod::create($data);
        });
    }

    public function update(
        PaymentMethod $paymentMethod,
        array $data
    ): PaymentMethod {
        return DB::transaction(function () use (
            $paymentMethod,
            $data
        ) {
            $paymentMethod->update($data);

            return $paymentMethod->refresh();
        });
    }

    public function delete(
        PaymentMethod $paymentMethod
    ): void {
        if ($paymentMethod->payments()->exists()) {
            throw ValidationException::withMessages([
                'payment_method' =>
                'This payment method is already used. Deactivate it instead.'
            ]);
        }

        $paymentMethod->delete();
    }
}
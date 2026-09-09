<?php

namespace App\Services\InvoiceItem;

use App\Models\InvoiceItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class InvoiceItemService
{
    public function paginate(
        array $filters = []
    ): LengthAwarePaginator {
        $query = InvoiceItem::query()
            ->with([
                'invoice',
                'studentFee'
            ]);

        if (!empty($filters['invoice_id'])) {
            $query->where(
                'invoice_id',
                $filters['invoice_id']
            );
        }

        if (!empty($filters['student_fee_id'])) {
            $query->where(
                'student_fee_id',
                $filters['student_fee_id']
            );
        }

        $perPage = min(
            max(
                (int) ($filters['per_page'] ?? 20),
                1
            ),
            100
        );

        return $query
            ->latest('id')
            ->paginate($perPage);
    }

    public function create(
        array $data
    ): InvoiceItem {
        return DB::transaction(
            function () use ($data) {
                $quantity =
                    (float) $data['quantity'];

                $unitAmount =
                    (float) $data['unit_amount'];

                $discount =
                    (float) (
                        $data['discount_amount']
                        ?? 0
                    );

                $subtotal =
                    $quantity * $unitAmount;

                if ($discount > $subtotal) {
                    throw new \InvalidArgumentException(
                        'Discount amount cannot be ' .
                        'greater than subtotal.'
                    );
                }

                $data['discount_amount'] =
                    $discount;

                $data['line_total'] =
                    $subtotal - $discount;

                $item = InvoiceItem::create($data);

                return $item->load([
                    'invoice',
                    'studentFee'
                ]);
            }
        );
    }

    public function update(
        InvoiceItem $item,
        array $data
    ): InvoiceItem {
        return DB::transaction(
            function () use ($item, $data) {
                $quantity = (float) (
                    $data['quantity']
                    ?? $item->quantity
                );

                $unitAmount = (float) (
                    $data['unit_amount']
                    ?? $item->unit_amount
                );

                $discount = (float) (
                    $data['discount_amount']
                    ?? $item->discount_amount
                    ?? 0
                );

                $subtotal =
                    $quantity * $unitAmount;

                if ($discount > $subtotal) {
                    throw new \InvalidArgumentException(
                        'Discount amount cannot be ' .
                        'greater than subtotal.'
                    );
                }

                $data['line_total'] =
                    $subtotal - $discount;

                $item->update($data);

                return $item
                    ->refresh()
                    ->load([
                        'invoice',
                        'studentFee'
                    ]);
            }
        );
    }

    public function delete(
        InvoiceItem $item
    ): void {
        DB::transaction(
            function () use ($item) {
                $item->delete();
            }
        );
    }
}
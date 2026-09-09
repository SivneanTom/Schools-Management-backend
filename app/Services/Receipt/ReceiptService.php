<?php

namespace App\Services\Receipt;

use App\Models\Payment;
use App\Models\Receipt;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ReceiptService
{
    public function paginate(array $filters = [])
    {
        $query = Receipt::query()
            ->with([
                'payment.invoice.student',
                'payment.paymentMethod',
                'payment.receivedBy'
            ]);

        if (!empty($filters['payment_id'])) {
            $query->where(
                'payment_id',
                $filters['payment_id']
            );
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate(
                'issued_at',
                '>=',
                $filters['date_from']
            );
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate(
                'issued_at',
                '<=',
                $filters['date_to']
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
            ->latest('issued_at')
            ->paginate($perPage);
    }

    public function create(array $data): Receipt
    {
        return DB::transaction(function () use ($data) {
            $payment = Payment::query()
                ->with([
                    'invoice',
                    'paymentMethod',
                    'receivedBy'
                ])
                ->lockForUpdate()
                ->findOrFail($data['payment_id']);

            if ($payment->status !== 'COMPLETED') {
                throw new InvalidArgumentException(
                    'Receipt can only be generated for a completed payment.'
                );
            }

            $exists = Receipt::query()
                ->where('payment_id', $payment->id)
                ->exists();

            if ($exists) {
                throw new InvalidArgumentException(
                    'A receipt already exists for this payment.'
                );
            }

            $issuedAt = isset($data['issued_at'])
                ? Carbon::parse($data['issued_at'])
                : now();

            if ($payment->paid_at) {
                $paidAt = Carbon::parse(
                    $payment->paid_at
                );

                if ($issuedAt->lt($paidAt)) {
                    throw new InvalidArgumentException(
                        'Receipt issued date cannot be earlier than payment date.'
                    );
                }
            }

            if (
                $payment->invoice
                && $payment->invoice->issued_date
            ) {
                $invoiceDate = Carbon::parse(
                    $payment->invoice->issued_date
                )->startOfDay();

                if ($issuedAt->lt($invoiceDate)) {
                    throw new InvalidArgumentException(
                        'Receipt issued date cannot be earlier than invoice issued date.'
                    );
                }
            }

            $receipt = Receipt::create([
                'payment_id' => $payment->id,
                'receipt_no' =>
                    $this->generateReceiptNo(),
                'issued_at' => $issuedAt,
                'file_url' =>
                    $data['file_url'] ?? null
            ]);

            return $receipt->load([
                'payment.invoice.student',
                'payment.paymentMethod',
                'payment.receivedBy'
            ]);
        });
    }

    private function generateReceiptNo(): string
    {
        do {
            $number =
                'REC-' .
                now()->format('Y') .
                '-' .
                strtoupper(
                    Str::random(10)
                );
        } while (
            Receipt::where(
                'receipt_no',
                $number
            )->exists()
        );

        return $number;
    }
}
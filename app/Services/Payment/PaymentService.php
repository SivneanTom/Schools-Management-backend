<?php

namespace App\Services\Payment;


use App\Models\Payment;
use App\Models\PaymentMethod;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    public function paginate(array $filters = []): LengthAwarePaginator
    {

        $query = Payment::query()
            ->with([
                'invoice',
                'paymentMethod',
                'receivedBy'
            ]);

        if (!empty($filters['invoice_id'])) {

            $query->where(
                'invoice_id',
                $filters['invoice_id']
            );
        }

        if (!empty($filters['payment_method_id'])) {

            $query->where(
                'payment_method_id',
                $filters['payment_method_id']
            );
        }

        if (!empty($filters['status'])) {

            $query->where(
                'status',
                strtoupper($filters['status'])
            );
        }

        return $query
            ->latest('id')
            ->paginate(
                $filters['per_page'] ?? 20
            );
    }
    public function create(array $data): Payment
    {

        return DB::transaction(function () use ($data) {
            $method = PaymentMethod::findOrFail(
                $data['payment_method_id']
            );

            if (!$method->is_active) {

                throw ValidationException::withMessages([

                    'payment_method_id'
                    =>
                    'Payment method is inactive.'

                ]);
            }

            $data['payment_no'] =
                $this->generatePaymentNo();

            $data['paid_at'] =
                $data['paid_at']
                ??
                now();

            $data['status'] =
                $data['status']
                ??
                Payment::STATUS_COMPLETED;

            $payment = Payment::create($data);

            /*
             | Update Invoice Status
             */

            $invoice = $payment->invoice;
            $paidAmount = $invoice
                ->payments()
                ->where(
                    'status',
                    Payment::STATUS_COMPLETED
                )
                ->sum('amount');

            if ($paidAmount >= $invoice->total_amount) {

                $invoice->update([

                    'status' => 'PAID'
                ]);
            } elseif ($paidAmount > 0) {

                $invoice->update([

                    'status' => 'PARTIAL'
                ]);
            }

            return $payment->load([

                'invoice',
                'paymentMethod',
                'receivedBy'

            ]);
        });
    }

    public function update(
        Payment $payment,
        array $data
    ): Payment {
        return DB::transaction(function () use (
            $payment,
            $data
        ) {

            if (isset($data['payment_method_id'])) {
                $method = PaymentMethod::findOrFail(
                    $data['payment_method_id']
                );

                if (!$method->is_active) {

                    throw ValidationException::withMessages([

                        'payment_method_id'
                        =>
                        'Payment method is inactive.'
                    ]);
                }
            }

            $payment->update($data);

            return $payment
                ->refresh()
                ->load([

                    'invoice',
                    'paymentMethod',
                    'receivedBy'

                ]);
        });
    }

    public function delete(
        Payment $payment
    ): void {


        if (
            $payment->status
            ===
            Payment::STATUS_COMPLETED
        ) {


            throw ValidationException::withMessages([

                'payment'
                =>
                'Completed payment cannot be deleted. Cancel it instead.'

            ]);
        }

        $payment->delete();
    }
    private function generatePaymentNo(): string
    {

        return 'PAY-'
            . date('Y')
            . '-'
            . strtoupper(
                uniqid()
            );
    }
}

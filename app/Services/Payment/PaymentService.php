<?php

namespace App\Services\Payment;


use App\Models\Payment;
use App\Models\PaymentMethod;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Services\Parent\ParentService;

class PaymentService
{
    public function __construct(
        private readonly ParentService $parentService
    ) {}
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
    // Parent Role 
    public function getForParentChild(User $user, int $studentId)
    {
        if (!$this->parentService->ownsChild($user, $studentId)) {
            abort(403, 'You can only view payments for your linked child.');
        }

        return Payment::query()
            ->whereHas('invoice', fn($q) => $q->where('student_id', $studentId))
            ->with(['invoice:id,invoice_no,total_amount,status', 'paymentMethod:id,code,name_km,name_en', 'receivedBy:id,staff_no,full_name_km,full_name_en,position_title_km,position_title_en'])
            ->orderByDesc('paid_at')
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'paymentNo' => $p->payment_no,
                'amount' => $p->amount,
                'paidAt' => $p->paid_at,
                'referenceNo' => $p->reference_no,
                'status' => $p->status,
                'invoice' => $p->invoice ? [
                    'id' => $p->invoice->id,
                    'invoiceNo' => $p->invoice->invoice_no,
                    'totalAmount' => $p->invoice->total_amount,
                    'status' => $p->invoice->status,
                ] : null,
                'paymentMethod' => $p->paymentMethod ? [
                    'id' => $p->paymentMethod->id,
                    'code' => $p->paymentMethod->code,
                    'nameKm' => $p->paymentMethod->name_km,
                    'nameEn' => $p->paymentMethod->name_en,
                ] : null,
                'receivedBy' => $p->receivedBy ? [
                    'staffNo' => $p->receivedBy->staff_no,
                    'fullNameKm' => $p->receivedBy->full_name_km,
                    'fullNameEn' => $p->receivedBy->full_name_en,
                    'positionTitleKm' => $p->receivedBy->position_title_km,
                    'positionTitleEn' => $p->receivedBy->position_title_en,
                ] : null,
            ]);
    }
}

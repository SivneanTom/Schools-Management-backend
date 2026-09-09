<?php

namespace App\Services\Payment;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Services\Parent\ParentService;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    public function __construct(private readonly ParentService $parentService) {}

    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $query = Payment::query()->with(['invoice','paymentMethod','receivedBy']);
        if (!empty($filters['invoice_id'])) $query->where('invoice_id', $filters['invoice_id']);
        if (!empty($filters['payment_method_id'])) $query->where('payment_method_id', $filters['payment_method_id']);
        if (!empty($filters['status'])) $query->where('status', strtoupper($filters['status']));
        if (!empty($filters['date_from'])) $query->whereDate('paid_at', '>=', $filters['date_from']);
        if (!empty($filters['date_to'])) $query->whereDate('paid_at', '<=', $filters['date_to']);
        if (!empty($filters['student_id'])) {
            $query->whereHas('invoice', fn($q) => $q->where('student_id', $filters['student_id']));
        }
        return $query->latest('id')->paginate((int) ($filters['per_page'] ?? 20));
    }

    public function create(array $data): Payment
    {
        return DB::transaction(function () use ($data) {
            $invoice = Invoice::query()->lockForUpdate()->findOrFail($data['invoice_id']);
            $method = PaymentMethod::findOrFail($data['payment_method_id']);
            if (!$method->is_active) $this->fail('payment_method_id', 'Payment method is inactive.');

            $amount = (float) ($data['amount'] ?? 0);
            if ($amount <= 0) $this->fail('amount', 'Payment amount must be greater than zero.');

            $paidAt = isset($data['paid_at']) ? Carbon::parse($data['paid_at']) : now();
            if ($invoice->issued_date && $paidAt->lt($invoice->issued_date->startOfDay())) {
                $this->fail('paid_at', 'Payment date cannot be before the invoice issued date.');
            }

            $status = strtoupper($data['status'] ?? Payment::STATUS_COMPLETED);
            $allowed = [Payment::STATUS_PENDING,Payment::STATUS_COMPLETED,Payment::STATUS_CANCELLED];
            if (!in_array($status, $allowed, true)) $this->fail('status', 'Invalid payment status.');

            if ($status === Payment::STATUS_COMPLETED) {
                $alreadyPaid = (float) $invoice->payments()->where('status', Payment::STATUS_COMPLETED)->sum('amount');
                if ($alreadyPaid + $amount > (float) $invoice->total_amount) {
                    $this->fail('amount', 'Payment amount exceeds the remaining invoice balance.');
                }
                if ($invoice->status === Invoice::STATUS_PAID) $this->fail('invoice_id', 'This invoice is already fully paid.');
            }

            $data['payment_no'] = $this->generatePaymentNo();
            $data['paid_at'] = $paidAt;
            $data['status'] = $status;
            $payment = Payment::create($data);
            $this->syncInvoiceStatus($invoice);
            return $payment->load(['invoice','paymentMethod','receivedBy']);
        });
    }

    public function update(Payment $payment, array $data): Payment
    {
        return DB::transaction(function () use ($payment, $data) {
            $invoice = Invoice::query()->lockForUpdate()->findOrFail($payment->invoice_id);

            if (isset($data['payment_method_id'])) {
                $method = PaymentMethod::findOrFail($data['payment_method_id']);
                if (!$method->is_active) $this->fail('payment_method_id', 'Payment method is inactive.');
            }

            $amount = array_key_exists('amount', $data) ? (float) $data['amount'] : (float) $payment->amount;
            if ($amount <= 0) $this->fail('amount', 'Payment amount must be greater than zero.');

            $paidAt = isset($data['paid_at']) ? Carbon::parse($data['paid_at']) : $payment->paid_at;
            if ($invoice->issued_date && $paidAt->lt($invoice->issued_date->startOfDay())) {
                $this->fail('paid_at', 'Payment date cannot be before the invoice issued date.');
            }

            $status = strtoupper($data['status'] ?? $payment->status);
            if (!in_array($status, [Payment::STATUS_PENDING,Payment::STATUS_COMPLETED,Payment::STATUS_CANCELLED], true)) {
                $this->fail('status', 'Invalid payment status.');
            }

            if ($status === Payment::STATUS_COMPLETED) {
                $otherPaid = (float) $invoice->payments()->whereKeyNot($payment->id)
                    ->where('status', Payment::STATUS_COMPLETED)->sum('amount');
                if ($otherPaid + $amount > (float) $invoice->total_amount) {
                    $this->fail('amount', 'Payment amount exceeds the remaining invoice balance.');
                }
            }

            $data['paid_at'] = $paidAt;
            $data['status'] = $status;
            $payment->update($data);
            $this->syncInvoiceStatus($invoice);
            return $payment->refresh()->load(['invoice','paymentMethod','receivedBy']);
        });
    }

    public function delete(Payment $payment): void
    {
        DB::transaction(function () use ($payment) {
            if ($payment->status === Payment::STATUS_COMPLETED) {
                $this->fail('payment', 'Completed payment cannot be deleted. Cancel it instead.');
            }
            $invoice = Invoice::query()->lockForUpdate()->findOrFail($payment->invoice_id);
            $payment->delete();
            $this->syncInvoiceStatus($invoice);
        });
    }

    private function syncInvoiceStatus(Invoice $invoice): void
    {
        $paid = (float) $invoice->payments()->where('status', Payment::STATUS_COMPLETED)->sum('amount');
        $status = $paid <= 0 ? Invoice::STATUS_UNPAID : ($paid >= (float) $invoice->total_amount ? Invoice::STATUS_PAID : Invoice::STATUS_PARTIAL);
        if ($invoice->status !== Invoice::STATUS_CANCELLED && $invoice->status !== $status) $invoice->update(['status' => $status]);
    }

    private function generatePaymentNo(): string
    {
        do {
            $no = 'PAY-'.now()->format('Y').'-'.strtoupper(bin2hex(random_bytes(5)));
        } while (Payment::where('payment_no', $no)->exists());
        return $no;
    }

    private function fail(string $field, string $message): never
    {
        throw ValidationException::withMessages([$field => $message]);
    }

    public function getForParentChild(User $user, int $studentId)
    {
        if (!$this->parentService->ownsChild($user, $studentId)) abort(403, 'You can only view payments for your linked child.');

        return Payment::query()
            ->whereHas('invoice', fn($q) => $q->where('student_id', $studentId))
            ->with([
                'invoice:id,invoice_no,total_amount,status',
                'paymentMethod:id,code,name_km,name_en',
                'receivedBy:id,staff_no,full_name_km,full_name_en,position_title_km,position_title_en'
            ])
            ->orderByDesc('paid_at')->get()->map(fn($p) => [
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
                    'status' => $p->invoice->status
                ] : null,
                'paymentMethod' => $p->paymentMethod ? [
                    'id' => $p->paymentMethod->id,
                    'code' => $p->paymentMethod->code,
                    'nameKm' => $p->paymentMethod->name_km,
                    'nameEn' => $p->paymentMethod->name_en
                ] : null,
                'receivedBy' => $p->receivedBy ? [
                    'staffNo' => $p->receivedBy->staff_no,
                    'fullNameKm' => $p->receivedBy->full_name_km,
                    'fullNameEn' => $p->receivedBy->full_name_en,
                    'positionTitleKm' => $p->receivedBy->position_title_km,
                    'positionTitleEn' => $p->receivedBy->position_title_en
                ] : null,
            ]);
    }
}

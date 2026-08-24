<?php

namespace App\Services\Receipt;

use App\Models\Receipt;
use App\Models\User;
use App\Services\Parent\ParentService;

class ReceiptService
{
    public function __construct(
        private readonly ParentService $parentService
    ) {}

    public function getForParentChild(User $user, int $studentId)
    {
        if (!$this->parentService->ownsChild($user, $studentId)) {
            abort(403, 'You can only view receipts for your linked child.');
        }

        return Receipt::query()
            ->whereHas('payment.invoice', fn($q) => $q->where('student_id', $studentId))
            ->with(['payment:id,invoice_id,payment_method_id,payment_no,amount,paid_at,status', 'payment.invoice:id,invoice_no,total_amount,status', 'payment.paymentMethod:id,code,name_km,name_en'])
            ->orderByDesc('issued_at')
            ->get()
            ->map(fn($r) => [
                'id' => $r->id,
                'receiptNo' => $r->receipt_no,
                'issuedAt' => $r->issued_at,
                'fileUrl' => $r->file_url,
                'payment' => $r->payment ? [
                    'paymentNo' => $r->payment->payment_no,
                    'amount' => $r->payment->amount,
                    'paidAt' => $r->payment->paid_at,
                    'status' => $r->payment->status,
                    'invoice' => $r->payment->invoice ? [
                        'invoiceNo' => $r->payment->invoice->invoice_no,
                        'totalAmount' => $r->payment->invoice->total_amount,
                        'status' => $r->payment->invoice->status,
                    ] : null,
                    'paymentMethod' => $r->payment->paymentMethod ? [
                        'code' => $r->payment->paymentMethod->code,
                        'nameKm' => $r->payment->paymentMethod->name_km,
                        'nameEn' => $r->payment->paymentMethod->name_en,
                    ] : null,
                ] : null,
            ]);
    }
}

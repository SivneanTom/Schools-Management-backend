<?php

namespace App\Services\Invoice;

use App\Models\Invoice;
use App\Models\User;
use App\Services\Parent\ParentService;

class InvoiceService
{
    public function __construct(
        private readonly ParentService $parentService
    ) {}
    public function create(array $data): Invoice
    {
        $data['invoice_no'] = $this->generateInvoiceNo();
        $data['discount_total'] = $data['discount_total'] ?? 0;
        $data['total_amount'] =
            $data['subtotal'] - $data['discount_total'];

        $data['status'] = $data['status'] ?? 'UNPAID';

        return Invoice::create($data);
    }

    private function generateInvoiceNo(): string
    {
        $year = date('Y');
        $count = Invoice::whereYear('created_at', $year)->count() + 1;

        return 'INV-' . $year . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);
    }

    public function update(Invoice $invoice, array $data): Invoice
    {
        if (isset($data['discount_total'])) {
            $data['total_amount'] =
                $invoice->subtotal - $data['discount_total'];
        }

        $invoice->update($data);

        return $invoice;
    }

    // Parent ROle
    public function getForParentChild(User $user, int $studentId)
    {
        if (!$this->parentService->ownsChild($user, $studentId)) {
            abort(403, 'You can only view invoices for your linked child.');
        }

        return Invoice::query()
            ->where('student_id', $studentId)
            ->with(['academicYear', 'items'])
            ->orderByDesc('issued_date')
            ->get()
            ->map(fn($invoice) => [
                'id' => $invoice->id,
                'invoiceNo' => $invoice->invoice_no,
                'issuedDate' => $invoice->issued_date,
                'dueDate' => $invoice->due_date,
                'subtotal' => $invoice->subtotal,
                'discountTotal' => $invoice->discount_total,
                'totalAmount' => $invoice->total_amount,
                'status' => $invoice->status,
                'academicYear' => [
                    'id' => $invoice->academicYear?->id,
                    'name' => $invoice->academicYear?->name,
                ],
                'items' => $invoice->items->map(fn($item) => [
                    'id' => $item->id,
                    'descriptionKm' => $item->description_km,
                    'descriptionEn' => $item->description_en,
                    'quantity' => $item->quantity,
                    'unitAmount' => $item->unit_amount,
                    'discountAmount' => $item->discount_amount,
                    'lineTotal' => $item->line_total,
                ]),
            ]);
    }
}

<?php

namespace App\Services\Invoice;

use App\Models\Invoice;

class InvoiceService
{
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
}

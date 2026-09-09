<?php

namespace App\Services\Invoice;

use App\Models\AcademicYear;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use App\Services\Parent\ParentService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InvoiceService
{
    public function __construct(private readonly ParentService $parentService) {}

    public function create(array $data): Invoice
    {
        return DB::transaction(function () use ($data) {
            $student = Student::findOrFail($data['student_id']);
            if ($student->status !== 'ACTIVE') $this->fail('student_id', 'The selected student is inactive.');
            if (!empty($data['academic_year_id'])) AcademicYear::findOrFail($data['academic_year_id']);

            $subtotal = (float) ($data['subtotal'] ?? 0);
            $discount = (float) ($data['discount_total'] ?? 0);
            $this->validateTotals($subtotal, $discount);
            $this->validateDates($data['issued_date'] ?? null, $data['due_date'] ?? null);

            $data['invoice_no'] = $this->generateInvoiceNo();
            $data['subtotal'] = $subtotal;
            $data['discount_total'] = $discount;
            $data['total_amount'] = $subtotal - $discount;
            $data['status'] = $data['status'] ?? Invoice::STATUS_UNPAID;
            return Invoice::create($data);
        });
    }

    public function update(Invoice $invoice, array $data): Invoice
    {
        return DB::transaction(function () use ($invoice, $data) {
            $hasCompletedPayment = $invoice->payments()->where('status', Payment::STATUS_COMPLETED)->exists();
            if ($hasCompletedPayment && $this->changesFinancialData($data)) {
                $this->fail('invoice', 'Paid or partially paid invoice financial data cannot be changed.');
            }

            if (isset($data['student_id'])) {
                $student = Student::findOrFail($data['student_id']);
                if ($student->status !== 'ACTIVE') $this->fail('student_id', 'The selected student is inactive.');
            }
            if (array_key_exists('academic_year_id', $data) && !empty($data['academic_year_id'])) {
                AcademicYear::findOrFail($data['academic_year_id']);
            }

            $subtotal = array_key_exists('subtotal', $data) ? (float) $data['subtotal'] : (float) $invoice->subtotal;
            $discount = array_key_exists('discount_total', $data) ? (float) $data['discount_total'] : (float) $invoice->discount_total;
            $this->validateTotals($subtotal, $discount);
            $this->validateDates($data['issued_date'] ?? $invoice->issued_date, $data['due_date'] ?? $invoice->due_date);

            $data['subtotal'] = $subtotal;
            $data['discount_total'] = $discount;
            $data['total_amount'] = $subtotal - $discount;
            $invoice->update($data);
            return $invoice->refresh();
        });
    }

    private function changesFinancialData(array $data): bool
    {
        foreach (['student_id','academic_year_id','issued_date','subtotal','discount_total','total_amount'] as $field) {
            if (array_key_exists($field, $data)) return true;
        }
        return false;
    }

    private function validateDates($issuedDate, $dueDate): void
    {
        if (!$issuedDate || !$dueDate) return;
        if (Carbon::parse($dueDate)->lt(Carbon::parse($issuedDate))) {
            $this->fail('due_date', 'Invoice due date cannot be before issued date.');
        }
    }

    private function generateInvoiceNo(): string
    {
        $year = now()->year;
        $count = Invoice::whereYear('created_at', $year)->count() + 1;
        return sprintf('INV-%d-%05d', $year, $count);
    }

    private function validateTotals(float $subtotal, float $discount): void
    {
        if ($subtotal < 0) $this->fail('subtotal', 'Invoice subtotal cannot be negative.');
        if ($discount < 0) $this->fail('discount_total', 'Invoice discount cannot be negative.');
        if ($discount > $subtotal) $this->fail('discount_total', 'Invoice discount cannot exceed the subtotal.');
    }

    private function fail(string $field, string $message): never
    {
        throw ValidationException::withMessages([$field => $message]);
    }

    public function getForParentChild(User $user, int $studentId)
    {
        if (!$this->parentService->ownsChild($user, $studentId)) abort(403, 'You can only view invoices for your linked child.');

        return Invoice::query()->where('student_id', $studentId)->with(['academicYear','items'])
            ->latest('id')->get()->map(fn($invoice) => [
                'id' => $invoice->id,
                'invoiceNo' => $invoice->invoice_no,
                'subtotal' => $invoice->subtotal,
                'discountTotal' => $invoice->discount_total,
                'totalAmount' => $invoice->total_amount,
                'status' => $invoice->status,
                'issuedDate' => $invoice->issued_date,
                'dueDate' => $invoice->due_date,
                'academicYear' => [
                    'id' => $invoice->academicYear?->id,
                    'name' => $invoice->academicYear?->name
                ],
                'items' => $invoice->items->map(fn($item) => [
                    'id' => $item->id,
                    'descriptionKm' => $item->description_km,
                    'descriptionEn' => $item->description_en,
                    'quantity' => $item->quantity,
                    'unitAmount' => $item->unit_amount,
                    'discountAmount' => $item->discount_amount,
                    'lineTotal' => $item->line_total
                ])->values()
            ]);
    }
}

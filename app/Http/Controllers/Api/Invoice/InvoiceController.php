<?php

namespace App\Http\Controllers\Api\Invoice;

use App\Http\Controllers\Controller;
use App\Http\Resources\Invoice\InvoiceResource;
use App\Models\Invoice;
use App\Services\Invoice\InvoiceService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly InvoiceService $invoiceService
    ) {}

    public function index(Request $request)
    {
        $perPage = min(
            max((int) $request->query('per_page', 20), 1),
            100
        );

        $query = Invoice::query()
            ->with([
                'student',
                'academicYear',
            ])
            ->when(
                $request->filled('student_id'),
                fn($q) => $q->where(
                    'student_id',
                    $request->student_id
                )
            )
            ->when(
                $request->filled('academic_year_id'),
                fn($q) => $q->where(
                    'academic_year_id',
                    $request->academic_year_id
                )
            )
            ->when(
                $request->filled('status'),
                fn($q) => $q->where(
                    'status',
                    $request->status
                )
            )
            ->when(
                $request->filled('due_from'),
                fn($q) => $q->whereDate(
                    'due_date',
                    '>=',
                    $request->due_from
                )
            )
            ->when(
                $request->filled('due_to'),
                fn($q) => $q->whereDate(
                    'due_date',
                    '<=',
                    $request->due_to
                )
            )
            ->orderByDesc('id');

        $invoices = $query->paginate($perPage);

        return $this->paginated(
            $invoices,
            InvoiceResource::collection(
                collect($invoices->items())
            )->resolve()
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'student_id' => [
                'required',
                'integer',
                'exists:students,id',
            ],
            'academic_year_id' => [
                'required',
                'integer',
                'exists:academic_years,id',
            ],
            'issued_date' => [
                'required',
                'date',
            ],
            'due_date' => [
                'required',
                'date',
                'after_or_equal:issued_date',
            ],
            'subtotal' => [
                'required',
                'numeric',
                'min:0',
            ],
            'discount_total' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'status' => [
                'nullable',
                'in:UNPAID,PARTIALLY_PAID,PAID,CANCELLED',
            ],
        ]);

        $invoice = $this->invoiceService
            ->create($data)
            ->load([
                'student',
                'academicYear',
            ]);

        return $this->success(
            new InvoiceResource($invoice),
            'Invoice created successfully.',
            201
        );
    }

    public function show(Invoice $invoice)
    {
        $invoice->load([
            'student',
            'academicYear',
            'items',
        ]);

        return $this->success(
            new InvoiceResource($invoice)
        );
    }

    public function update(
        Request $request,
        Invoice $invoice
    ) {
        $data = $request->validate([
            'student_id' => [
                'sometimes',
                'integer',
                'exists:students,id',
            ],
            'academic_year_id' => [
                'sometimes',
                'integer',
                'exists:academic_years,id',
            ],
            'issued_date' => [
                'sometimes',
                'date',
            ],
            'due_date' => [
                'sometimes',
                'date',
            ],
            'subtotal' => [
                'sometimes',
                'numeric',
                'min:0',
            ],
            'discount_total' => [
                'sometimes',
                'numeric',
                'min:0',
            ],
            'status' => [
                'sometimes',
                'in:UNPAID,PARTIALLY_PAID,PAID,CANCELLED',
            ],
        ]);

        $invoice = $this->invoiceService
            ->update($invoice, $data)
            ->load([
                'student',
                'academicYear',
            ]);

        return $this->success(
            new InvoiceResource($invoice),
            'Invoice updated successfully.'
        );
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();

        return $this->success(
            null,
            'Invoice deleted successfully.'
        );
    }
}

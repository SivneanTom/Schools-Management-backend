<?php

namespace App\Http\Controllers\Api\Invoice;

use App\Http\Controllers\Controller;
use App\Http\Requests\Invoice\StoreInvoiceRequest;
use App\Http\Requests\Invoice\UpdateInvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Services\Invoice\InvoiceService;

class InvoiceController extends Controller
{
    public function __construct(
        private InvoiceService $service
    ) {}

    public function index()
    {
        return InvoiceResource::collection(
            Invoice::with([
                'student:id,student_code,first_name_km,last_name_km,first_name_en,last_name_en',
                'academicYear:id,name'
            ])->paginate(20)
        );
    }

    public function store(StoreInvoiceRequest $request)
    {
        return new InvoiceResource(
            $this->service->create($request->validated())
        );
    }

    public function show(Invoice $invoice)
    {
        return new InvoiceResource(
            $invoice->load(['student', 'academicYear'])
        );
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice)
    {
        return new InvoiceResource(
            $this->service->update($invoice, $request->validated())
        );
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();

        return response()->json([
            'message' => 'Invoice deleted'
        ]);
    }
}

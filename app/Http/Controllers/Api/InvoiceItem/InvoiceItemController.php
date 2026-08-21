<?php

namespace App\Http\Controllers\Api\InvoiceItem;

use App\Http\Controllers\Controller;
use App\Http\Requests\InvoiceItem\StoreInvoiceItemRequest;
use App\Http\Requests\InvoiceItem\UpdateInvoiceItemRequest;
use App\Http\Resources\InvoiceItemResource;
use App\Models\InvoiceItem;
use App\Services\InvoiceItem\InvoiceItemService;

class InvoiceItemController extends Controller
{
    public function __construct(
        private InvoiceItemService $service
    ){}

    public function index()
    {
        return InvoiceItemResource::collection(
            InvoiceItem::paginate(20)
        );
    }

    public function store(StoreInvoiceItemRequest $request)
    {
        return new InvoiceItemResource(
            $this->service->create($request->validated())
        );
    }

    public function show(InvoiceItem $invoiceItem)
    {
        return new InvoiceItemResource($invoiceItem);
    }

    public function update(UpdateInvoiceItemRequest $request, InvoiceItem $invoiceItem)
    {
        return new InvoiceItemResource(
            $this->service->update($invoiceItem,$request->validated())
        );
    }

    public function destroy(InvoiceItem $invoiceItem)
    {
        $invoiceItem->delete();

        return response()->json([
            'message'=>'Invoice item deleted'
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api\InvoiceItem;

use App\Http\Controllers\Controller;
use App\Http\Resources\InvoiceItemResource;
use App\Models\InvoiceItem;
use App\Services\InvoiceItem\InvoiceItemService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class InvoiceItemController extends Controller
{
    public function __construct(
        private InvoiceItemService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'invoice_id' => [
                'nullable',
                'integer',
                'exists:invoices,id'
            ],
            'student_fee_id' => [
                'nullable',
                'integer',
                'exists:student_fees,id'
            ],
            'per_page' => [
                'nullable',
                'integer',
                'min:1',
                'max:100'
            ]
        ]);

        $items = $this->service->paginate(
            $request->only([
                'invoice_id',
                'student_fee_id',
                'per_page'
            ])
        );

        return response()->json([
            'success' => true,
            'data' => InvoiceItemResource::collection(
                $items->items()
            ),
            'pagination' => [
                'current_page' => $items->currentPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
                'last_page' => $items->lastPage()
            ]
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'invoice_id' => [
                'required',
                'integer',
                'exists:invoices,id'
            ],
            'student_fee_id' => [
                'nullable',
                'integer',
                'exists:student_fees,id'
            ],
            'description_km' => [
                'nullable',
                'string',
                'max:255'
            ],
            'description_en' => [
                'nullable',
                'string',
                'max:255'
            ],
            'quantity' => [
                'required',
                'numeric',
                'gt:0'
            ],
            'unit_amount' => [
                'required',
                'numeric',
                'min:0'
            ],
            'discount_amount' => [
                'nullable',
                'numeric',
                'min:0'
            ]
        ]);

        try {
            $item = $this->service->create($data);

            return response()->json([
                'success' => true,
                'message' =>
                    'Invoice item created successfully.',
                'data' => new InvoiceItemResource($item)
            ], 201);
        } catch (\InvalidArgumentException $e) {
            throw ValidationException::withMessages([
                'invoice_item' => [$e->getMessage()]
            ]);
        }
    }

    public function show(
        InvoiceItem $invoiceItem
    ): JsonResponse {
        $invoiceItem->load([
            'invoice',
            'studentFee'
        ]);

        return response()->json([
            'success' => true,
            'data' => new InvoiceItemResource(
                $invoiceItem
            )
        ]);
    }

    public function update(
        Request $request,
        InvoiceItem $invoiceItem
    ): JsonResponse {
        $data = $request->validate([
            'invoice_id' => [
                'sometimes',
                'integer',
                'exists:invoices,id'
            ],
            'student_fee_id' => [
                'nullable',
                'integer',
                'exists:student_fees,id'
            ],
            'description_km' => [
                'nullable',
                'string',
                'max:255'
            ],
            'description_en' => [
                'nullable',
                'string',
                'max:255'
            ],
            'quantity' => [
                'sometimes',
                'numeric',
                'gt:0'
            ],
            'unit_amount' => [
                'sometimes',
                'numeric',
                'min:0'
            ],
            'discount_amount' => [
                'nullable',
                'numeric',
                'min:0'
            ]
        ]);

        try {
            $item = $this->service->update(
                $invoiceItem,
                $data
            );

            return response()->json([
                'success' => true,
                'message' =>
                    'Invoice item updated successfully.',
                'data' => new InvoiceItemResource($item)
            ]);
        } catch (\InvalidArgumentException $e) {
            throw ValidationException::withMessages([
                'invoice_item' => [$e->getMessage()]
            ]);
        }
    }

    public function destroy(
        InvoiceItem $invoiceItem
    ): JsonResponse {
        $this->service->delete($invoiceItem);

        return response()->json([
            'success' => true,
            'message' =>
                'Invoice item deleted successfully.'
        ]);
    }
}
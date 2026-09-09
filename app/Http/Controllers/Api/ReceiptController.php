<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReceiptResource;
use App\Models\Receipt;
use App\Services\Receipt\ReceiptService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReceiptController extends Controller
{
    public function __construct(
        private ReceiptService $service
    ) {}

    public function index(
        Request $request
    ): JsonResponse {
        $request->validate([
            'payment_id' => [
                'nullable',
                'integer',
                'exists:payments,id'
            ],
            'date_from' => [
                'nullable',
                'date'
            ],
            'date_to' => [
                'nullable',
                'date',
                'after_or_equal:date_from'
            ],
            'per_page' => [
                'nullable',
                'integer',
                'min:1',
                'max:100'
            ]
        ]);

        $receipts = $this->service->paginate(
            $request->only([
                'payment_id',
                'date_from',
                'date_to',
                'per_page'
            ])
        );

        return response()->json([
            'success' => true,

            'data' => ReceiptResource::collection(
                $receipts->items()
            ),

            'pagination' => [
                'current_page' =>
                    $receipts->currentPage(),
                'per_page' =>
                    $receipts->perPage(),
                'total' =>
                    $receipts->total(),
                'last_page' =>
                    $receipts->lastPage()
            ]
        ]);
    }

    public function store(
        Request $request
    ): JsonResponse {
        $data = $request->validate([
            'payment_id' => [
                'required',
                'integer',
                'exists:payments,id'
            ],
            'issued_at' => [
                'nullable',
                'date'
            ],
            'file_url' => [
                'nullable',
                'string',
                'max:2048'
            ]
        ]);

        try {
            $receipt =
                $this->service->create($data);

            return response()->json([
                'success' => true,
                'message' =>
                    'Receipt generated successfully.',
                'data' =>
                    new ReceiptResource($receipt)
            ], 201);
        } catch (\InvalidArgumentException $e) {
            throw ValidationException::withMessages([
                'receipt' => [
                    $e->getMessage()
                ]
            ]);
        }
    }

    public function show(
        Receipt $receipt
    ): JsonResponse {
        $receipt->load([
            'payment.invoice.student',
            'payment.paymentMethod',
            'payment.receivedBy'
        ]);

        return response()->json([
            'success' => true,
            'data' =>
                new ReceiptResource($receipt)
        ]);
    }
}
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReceiptRequest;
use App\Http\Resources\ReceiptResource;
use App\Models\Receipt;
use Illuminate\Http\Request;

class ReceiptController extends Controller
{

    public function index(Request $request)
    {

        $receipts = Receipt::with('payment')
            ->latest()
            ->paginate(
                $request->get('per_page', 20)
            );

        return response()->json([
            'success' => true,

            'data' => ReceiptResource::collection(
                $receipts
            ),

            'pagination' => [
                'current_page' => $receipts->currentPage(),
                'per_page' => $receipts->perPage(),
                'total' => $receipts->total(),
                'last_page' => $receipts->lastPage(),
            ]
        ]);
    }

    public function store(StoreReceiptRequest $request)
    {

        $receipt = Receipt::create([

            'payment_id' => $request->payment_id,

            'receipt_no' => $request->receipt_no,

            'issued_at' => $request->issued_at,

            'file_url' => $request->file_url,

        ]);

        return response()->json([

            'success' => true,

            'message' => 'Receipt created successfully',

            'data' => new ReceiptResource(
                $receipt->load('payment')
            )

        ], 201);
    }

    public function show($id)
    {

        $receipt = Receipt::with('payment')
            ->findOrFail($id);

        return response()->json([

            'success' => true,

            'data' => new ReceiptResource($receipt)

        ]);
    }
}

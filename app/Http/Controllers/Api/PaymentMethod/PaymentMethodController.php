<?php

namespace App\Http\Controllers\Api\PaymentMethod;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentMethod\StorePaymentMethodRequest;
use App\Http\Requests\PaymentMethod\UpdatePaymentMethodRequest;
use App\Http\Resources\PaymentMethodResource;
use App\Models\PaymentMethod;
use App\Services\PaymentMethod\PaymentMethodService;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    public function __construct(
        private readonly PaymentMethodService $service
    ) {
    }

    /**
     * Get all payment methods
     */
    public function index(Request $request)
    {
        $paymentMethods = $this->service->paginate(
            $request->all()
        );

        return response()->json([
            'success' => true,
            'data' => PaymentMethodResource::collection(
                $paymentMethods->items()
            ),
            'pagination' => [
                'current_page' => $paymentMethods->currentPage(),
                'per_page' => $paymentMethods->perPage(),
                'total' => $paymentMethods->total(),
                'last_page' => $paymentMethods->lastPage(),
            ]
        ]);
    }

    /**
     * Create payment method
     */
    public function store(StorePaymentMethodRequest $request)
    {
        $paymentMethod = $this->service->create(
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Payment method created successfully.',
            'data' => new PaymentMethodResource(
                $paymentMethod
            )
        ], 201);
    }

    /**
     * Show payment method
     */
    public function show(PaymentMethod $paymentMethod)
    {
        return response()->json([
            'success' => true,
            'data' => new PaymentMethodResource(
                $paymentMethod
            )
        ]);
    }
    /**
     * Update payment method
     */
    public function update(
        UpdatePaymentMethodRequest $request,
        PaymentMethod $paymentMethod
    )
    {

        $paymentMethod = $this->service->update(
            $paymentMethod,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Payment method updated successfully.',
            'data' => new PaymentMethodResource(
                $paymentMethod
            )
        ]);
    }
    /**
     * Delete payment method
     */
    public function destroy(PaymentMethod $paymentMethod)
    {

        $this->service->delete(
            $paymentMethod
        );


        return response()->json([
            'success' => true,
            'message' => 'Payment method deleted successfully.'
        ]);
    }
}
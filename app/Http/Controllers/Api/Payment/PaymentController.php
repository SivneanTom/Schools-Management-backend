<?php

namespace App\Http\Controllers\Api\Payment;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\StorePaymentRequest;
use App\Http\Requests\Payment\UpdatePaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use App\Services\Payment\PaymentService;
use Illuminate\Http\Request;


class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentService $service
    ) {
    }

    /**
     * Get payments
     */
    public function index(Request $request)
    {

        $payments = $this->service->paginate(
            $request->all()
        );


        return response()->json([
            'success' => true,

            'data' => PaymentResource::collection(
                $payments->items()
            ),

            'pagination'=>[
                'current_page'=>$payments->currentPage(),
                'per_page'=>$payments->perPage(),
                'total'=>$payments->total(),
                'last_page'=>$payments->lastPage()
            ]
        ]);
    }


    /**
     * Create payment
     */
    public function store(StorePaymentRequest $request)
    {

        $payment = $this->service->create(
            $request->validated()
        );


        return response()->json([
            'success'=>true,

            'message'=>'Payment created successfully.',

            'data'=>new PaymentResource(
                $payment
            )
        ],201);
    }

    /**
     * Show payment
     */
    public function show(Payment $payment)
    {

        $payment->load([
            'invoice',
            'paymentMethod',
            'receivedBy'
        ]);


        return response()->json([
            'success'=>true,

            'data'=>new PaymentResource(
                $payment
            )
        ]);
    }

    /**
     * Update payment
     */
    public function update(
        UpdatePaymentRequest $request,
        Payment $payment
    )
    {

        $payment = $this->service->update(
            $payment,
            $request->validated()
        );

        return response()->json([
            'success'=>true,

            'message'=>'Payment updated successfully.',

            'data'=>new PaymentResource(
                $payment
            )
        ]);
    }

    /**
     * Delete payment
     */
    public function destroy(Payment $payment)
    {

        $this->service->delete(
            $payment
        );


        return response()->json([
            'success'=>true,

            'message'=>'Payment deleted successfully.'
        ]);
    }

}
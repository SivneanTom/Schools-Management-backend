<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Controller;
use App\Services\Finance\AccountantFinanceService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AccountantFinanceController extends Controller
{
    public function __construct(
        private readonly AccountantFinanceService $service
    ) {}

    public function students(Request $request): JsonResponse
    {
        return $this->paginated(
            $this->service->students(
                $request->only('search', 'per_page')
            )
        );
    }

    public function dashboard(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->service->dashboard(
                $this->filters($request)
            ),
        ]);
    }

    public function revenue(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->service->revenue(
                $this->filters($request)
            ),
        ]);
    }

    public function outstandingFees(Request $request): JsonResponse
    {
        $paginator = $this->service->outstandingFees(
            $this->filters($request)
        );

        $data = collect($paginator->items())->map(
            fn ($invoice) => [
                'invoice_id' => $invoice->id,
                'invoice_no' => $invoice->invoice_no,
                'student' => [
                    'id' => $invoice->student?->id,
                    'student_code' => $invoice->student?->student_code,
                    'full_name_km' => trim(
                        ($invoice->student?->first_name_km ?? '')
                        .' '.
                        ($invoice->student?->last_name_km ?? '')
                    ),
                    'full_name_en' => trim(
                        ($invoice->student?->first_name_en ?? '')
                        .' '.
                        ($invoice->student?->last_name_en ?? '')
                    ),
                ],
                'academic_year' => $invoice->academicYear ? [
                    'id' => $invoice->academicYear->id,
                    'name' => $invoice->academicYear->name,
                ] : null,
                'total_amount' => number_format(
                    (float) $invoice->total_amount,
                    2,
                    '.',
                    ''
                ),
                'paid_amount' => number_format(
                    (float) ($invoice->paid_amount ?? 0),
                    2,
                    '.',
                    ''
                ),
                'outstanding_amount' => number_format(
                    max(
                        (float) $invoice->total_amount
                        - (float) ($invoice->paid_amount ?? 0),
                        0
                    ),
                    2,
                    '.',
                    ''
                ),
                'status' => $invoice->status,
                'due_date' => $invoice->due_date?->toDateString(),
            ]
        );

        return $this->paginated(
            $paginator,
            $data
        );
    }

    public function reports(Request $request): JsonResponse
    {
        $result = $this->service->report(
            $this->filters($request)
        );

        $paginator = $result['payments'];

        $data = collect($paginator->items())->map(
            fn ($payment) => [
                'id' => $payment->id,
                'payment_no' => $payment->payment_no,
                'invoice_no' => $payment->invoice?->invoice_no,
                'student_code' => $payment->invoice?->student?->student_code,
                'student_name_en' => trim(
                    ($payment->invoice?->student?->first_name_en ?? '')
                    .' '.
                    ($payment->invoice?->student?->last_name_en ?? '')
                ),
                'payment_method' => $payment->paymentMethod?->code,
                'amount' => number_format(
                    (float) $payment->amount,
                    2,
                    '.',
                    ''
                ),
                'paid_at' => $payment->paid_at?->format('Y-m-d H:i:s'),
                'reference_no' => $payment->reference_no,
                'received_by' => $payment->receivedBy?->full_name_en,
                'status' => $payment->status,
            ]
        );

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => $result['summary'],
                'payments' => $data,
            ],
            'pagination' => $this->pagination($paginator),
        ]);
    }

    public function calculateDiscount(
        Request $request
    ): JsonResponse {
        $data = $request->validate([
            'student_scholarship_id' => [
                'required',
                'integer',
                'exists:student_scholarships,id',
            ],
            'amount' => [
                'required',
                'numeric',
                'min:0',
            ],
            'date' => [
                'nullable',
                'date',
            ],
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->service->calculateDiscount(
                (int) $data['student_scholarship_id'],
                (float) $data['amount'],
                $data['date'] ?? null
            ),
        ]);
    }

    public function exportPayments(
        Request $request
    ): StreamedResponse {
        $rows = $this->service->exportRows(
            $this->filters($request)
        );

        $filename = 'payment-report-'
            .now()->format('Ymd-His')
            .'.csv';

        return response()->streamDownload(
            function () use ($rows) {
                $out = fopen('php://output', 'w');

                fwrite($out, "\xEF\xBB\xBF");

                fputcsv($out, [
                    'Payment No',
                    'Invoice No',
                    'Student Code',
                    'Student Name',
                    'Payment Method',
                    'Amount',
                    'Paid At',
                    'Reference No',
                    'Received By',
                    'Status',
                ]);

                foreach ($rows as $payment) {
                    fputcsv($out, [
                        $payment->payment_no,
                        $payment->invoice?->invoice_no,
                        $payment->invoice?->student?->student_code,
                        trim(
                            ($payment->invoice?->student?->first_name_en ?? '')
                            .' '.
                            ($payment->invoice?->student?->last_name_en ?? '')
                        ),
                        $payment->paymentMethod?->code,
                        $payment->amount,
                        $payment->paid_at?->format('Y-m-d H:i:s'),
                        $payment->reference_no,
                        $payment->receivedBy?->full_name_en,
                        $payment->status,
                    ]);
                }

                fclose($out);
            },
            $filename,
            [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ]
        );
    }

    private function filters(Request $request): array
    {
        return $request->only(
            'student_id',
            'academic_year_id',
            'payment_method_id',
            'date_from',
            'date_to',
            'per_page'
        );
    }

    private function paginated(
        LengthAwarePaginator $paginator,
        Collection|array|null $data = null
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'data' => $data ?? $paginator->items(),
            'pagination' => $this->pagination($paginator),
        ]);
    }

    private function pagination(
        LengthAwarePaginator $paginator
    ): array {
        return [
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'last_page' => $paginator->lastPage(),
        ];
    }
}
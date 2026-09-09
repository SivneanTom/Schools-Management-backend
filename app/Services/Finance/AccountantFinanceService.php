<?php

namespace App\Services\Finance;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Student;
use App\Models\StudentScholarship;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class AccountantFinanceService
{
    public function students(
        array $filters = []
    ): LengthAwarePaginator {
        return Student::query()
            ->where('status', 'ACTIVE')
            ->when(
                !empty($filters['search']),
                function (
                    Builder $query
                ) use ($filters) {
                    $search = trim(
                        $filters['search']
                    );

                    $query->where(
                        function (
                            Builder $q
                        ) use ($search) {
                            $q->where(
                                'student_code',
                                'ilike',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'first_name_km',
                                'ilike',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'last_name_km',
                                'ilike',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'first_name_en',
                                'ilike',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'last_name_en',
                                'ilike',
                                "%{$search}%"
                            );
                        }
                    );
                }
            )
            ->select([
                'id',
                'student_code',
                'first_name_km',
                'last_name_km',
                'first_name_en',
                'last_name_en',
                'status'
            ])
            ->orderBy('id')
            ->paginate(
                (int) (
                    $filters['per_page']
                    ?? 20
                )
            );
    }

    public function dashboard(
        array $filters = []
    ): array {
        $payments =
            $this->completedPayments(
                $filters
            );

        $totalRevenue =
            (float) (
                clone $payments
            )->sum('amount');

        $todayRevenue =
            (float) (
                clone $payments
            )
            ->whereDate(
                'paid_at',
                today()
            )
            ->sum('amount');

        $monthRevenue =
            (float) (
                clone $payments
            )
            ->whereYear(
                'paid_at',
                now()->year
            )
            ->whereMonth(
                'paid_at',
                now()->month
            )
            ->sum('amount');

        $invoiceQuery =
            $this->invoiceQuery(
                $filters
            );

        return [
            'total_revenue' =>
                number_format(
                    $totalRevenue,
                    2,
                    '.',
                    ''
                ),
            'revenue_today' =>
                number_format(
                    $todayRevenue,
                    2,
                    '.',
                    ''
                ),
            'revenue_this_month' =>
                number_format(
                    $monthRevenue,
                    2,
                    '.',
                    ''
                ),
            'outstanding_amount' =>
                number_format(
                    $this->outstandingTotal(
                        $filters
                    ),
                    2,
                    '.',
                    ''
                ),
            'paid_invoices' =>
                (clone $invoiceQuery)
                    ->where(
                        'status',
                        Invoice::STATUS_PAID
                    )
                    ->count(),
            'unpaid_invoices' =>
                (clone $invoiceQuery)
                    ->where(
                        'status',
                        Invoice::STATUS_UNPAID
                    )
                    ->count(),
            'partial_invoices' =>
                (clone $invoiceQuery)
                    ->where(
                        'status',
                        Invoice::STATUS_PARTIAL
                    )
                    ->count(),
            'completed_payments' =>
                (clone $payments)->count(),
            'active_scholarships' =>
                StudentScholarship::where(
                    'status',
                    StudentScholarship::STATUS_ACTIVE
                )->count()
        ];
    }

    public function revenue(
        array $filters = []
    ): array {
        $query =
            $this->completedPayments(
                $filters
            );

        $total =
            (float) (
                clone $query
            )->sum('amount');

        $byMethod =
            (clone $query)
            ->join(
                'payment_methods as pm',
                'pm.id',
                '=',
                'payments.payment_method_id'
            )
            ->select(
                'pm.id',
                'pm.code',
                'pm.name_km',
                'pm.name_en'
            )
            ->selectRaw(
                'SUM(payments.amount) as amount'
            )
            ->groupBy(
                'pm.id',
                'pm.code',
                'pm.name_km',
                'pm.name_en'
            )
            ->orderByDesc('amount')
            ->get();

        return [
            'total_revenue' =>
                number_format(
                    $total,
                    2,
                    '.',
                    ''
                ),
            'by_payment_method' =>
                $byMethod->map(
                    fn($row) => [
                        'id' => $row->id,
                        'code' => $row->code,
                        'name_km' =>
                            $row->name_km,
                        'name_en' =>
                            $row->name_en,
                        'amount' =>
                            number_format(
                                (float) $row->amount,
                                2,
                                '.',
                                ''
                            )
                    ]
                )->values()
        ];
    }

    public function outstandingFees(
        array $filters = []
    ): LengthAwarePaginator {
        return $this->invoiceQuery(
            $filters
        )
        ->whereIn(
            'invoices.status',
            [
                Invoice::STATUS_UNPAID,
                Invoice::STATUS_PARTIAL
            ]
        )
        ->with([
            'student:id,student_code,first_name_km,last_name_km,first_name_en,last_name_en',
            'academicYear:id,name'
        ])
        ->withSum(
            [
                'payments as paid_amount' =>
                    fn($q) =>
                        $q->where(
                            'status',
                            Payment::STATUS_COMPLETED
                        )
            ],
            'amount'
        )
        ->latest('invoices.id')
        ->paginate(
            (int) (
                $filters['per_page']
                ?? 20
            )
        );
    }

    public function report(
        array $filters = []
    ): array {
        $query =
            $this->completedPayments(
                $filters
            )
            ->with([
                'invoice.student',
                'paymentMethod',
                'receivedBy'
            ]);

        $total =
            (float) (
                clone $query
            )->sum('amount');

        $count =
            (clone $query)->count();

        $payments =
            $query
            ->latest('paid_at')
            ->paginate(
                (int) (
                    $filters['per_page']
                    ?? 20
                )
            );

        return [
            'summary' => [
                'completed_payments' =>
                    $count,
                'total_amount' =>
                    number_format(
                        $total,
                        2,
                        '.',
                        ''
                    )
            ],
            'payments' => $payments
        ];
    }

    public function exportRows(
        array $filters = []
    ) {
        return $this->completedPayments(
            $filters
        )
        ->with([
            'invoice.student',
            'paymentMethod',
            'receivedBy'
        ])
        ->orderBy('paid_at')
        ->get();
    }

    public function calculateDiscount(
        int $studentScholarshipId,
        float $amount,
        ?string $date = null
    ): array {
        if ($amount < 0) {
            $this->fail(
                'amount',
                'Amount cannot be negative.'
            );
        }

        $award =
            StudentScholarship::with(
                'scholarship'
            )->findOrFail(
                $studentScholarshipId
            );

        $scholarship =
            $award->scholarship;

        $applyDate =
            $date
            ? Carbon::parse(
                $date
            )->startOfDay()
            : now()->startOfDay();

        if (
            $award->status
            !==
            StudentScholarship::STATUS_ACTIVE
        ) {
            $this->fail(
                'student_scholarship_id',
                'Student scholarship is not active.'
            );
        }

        if (
            !$scholarship
            ||
            !$scholarship->is_active
        ) {
            $this->fail(
                'student_scholarship_id',
                'Scholarship is inactive.'
            );
        }

        if (
            $award->awarded_at
            &&
            $applyDate->lt(
                $award->awarded_at
                    ->startOfDay()
            )
        ) {
            $this->fail(
                'date',
                'Discount date cannot be before scholarship award date.'
            );
        }

        if (
            $scholarship->start_date
            &&
            $applyDate->lt(
                $scholarship->start_date
                    ->startOfDay()
            )
        ) {
            $this->fail(
                'date',
                'Scholarship has not started yet.'
            );
        }

        if (
            $scholarship->end_date
            &&
            $applyDate->gt(
                $scholarship->end_date
                    ->endOfDay()
            )
        ) {
            $this->fail(
                'date',
                'Scholarship has expired.'
            );
        }

        $value =
            (float) $scholarship
                ->discount_value;

        $discount =
            $scholarship->discount_type
            === 'PERCENTAGE'
            ? $amount * ($value / 100)
            : $value;

        $discount =
            min(
                $discount,
                $amount
            );

        return [
            'student_scholarship_id' =>
                $award->id,
            'student_id' =>
                $award->student_id,
            'scholarship_id' =>
                $scholarship->id,
            'discount_type' =>
                $scholarship->discount_type,
            'discount_value' =>
                number_format(
                    $value,
                    2,
                    '.',
                    ''
                ),
            'original_amount' =>
                number_format(
                    $amount,
                    2,
                    '.',
                    ''
                ),
            'discount_amount' =>
                number_format(
                    $discount,
                    2,
                    '.',
                    ''
                ),
            'final_amount' =>
                number_format(
                    $amount - $discount,
                    2,
                    '.',
                    ''
                ),
            'applied_date' =>
                $applyDate->toDateString()
        ];
    }

    private function completedPayments(
        array $filters = []
    ): Builder {
        return Payment::query()
            ->where(
                'payments.status',
                Payment::STATUS_COMPLETED
            )
            ->when(
                !empty(
                    $filters['date_from']
                ),
                fn($q) =>
                    $q->whereDate(
                        'paid_at',
                        '>=',
                        $filters['date_from']
                    )
            )
            ->when(
                !empty(
                    $filters['date_to']
                ),
                fn($q) =>
                    $q->whereDate(
                        'paid_at',
                        '<=',
                        $filters['date_to']
                    )
            )
            ->when(
                !empty(
                    $filters[
                        'payment_method_id'
                    ]
                ),
                fn($q) =>
                    $q->where(
                        'payment_method_id',
                        $filters[
                            'payment_method_id'
                        ]
                    )
            )
            ->when(
                !empty(
                    $filters['student_id']
                ),
                fn($q) =>
                    $q->whereHas(
                        'invoice',
                        fn($x) =>
                            $x->where(
                                'student_id',
                                $filters[
                                    'student_id'
                                ]
                            )
                    )
            )
            ->when(
                !empty(
                    $filters[
                        'academic_year_id'
                    ]
                ),
                fn($q) =>
                    $q->whereHas(
                        'invoice',
                        fn($x) =>
                            $x->where(
                                'academic_year_id',
                                $filters[
                                    'academic_year_id'
                                ]
                            )
                    )
            );
    }

    private function invoiceQuery(
        array $filters = []
    ): Builder {
        return Invoice::query()
            ->when(
                !empty(
                    $filters['student_id']
                ),
                fn($q) =>
                    $q->where(
                        'student_id',
                        $filters[
                            'student_id'
                        ]
                    )
            )
            ->when(
                !empty(
                    $filters[
                        'academic_year_id'
                    ]
                ),
                fn($q) =>
                    $q->where(
                        'academic_year_id',
                        $filters[
                            'academic_year_id'
                        ]
                    )
            )
            ->when(
                !empty(
                    $filters['date_from']
                ),
                fn($q) =>
                    $q->whereDate(
                        'issued_date',
                        '>=',
                        $filters[
                            'date_from'
                        ]
                    )
            )
            ->when(
                !empty(
                    $filters['date_to']
                ),
                fn($q) =>
                    $q->whereDate(
                        'issued_date',
                        '<=',
                        $filters[
                            'date_to'
                        ]
                    )
            );
    }

    private function outstandingTotal(
        array $filters = []
    ): float {
        return (float)
            $this->invoiceQuery(
                $filters
            )
            ->whereIn(
                'invoices.status',
                [
                    Invoice::STATUS_UNPAID,
                    Invoice::STATUS_PARTIAL
                ]
            )
            ->withSum(
                [
                    'payments as paid_amount' =>
                        fn($q) =>
                            $q->where(
                                'status',
                                Payment::STATUS_COMPLETED
                            )
                ],
                'amount'
            )
            ->get()
            ->sum(
                fn($invoice) =>
                    max(
                        (float)
                            $invoice->total_amount
                        -
                        (float) (
                            $invoice->paid_amount
                            ?? 0
                        ),
                        0
                    )
            );
    }

    private function fail(
        string $field,
        string $message
    ): never {
        throw ValidationException::withMessages([
            $field => $message
        ]);
    }
}
<?php

use App\Http\Controllers\Api\FeeType\FeeTypeController;
use App\Http\Controllers\Api\Finance\AccountantFinanceController;
use App\Http\Controllers\Api\Invoice\InvoiceController;
use App\Http\Controllers\Api\InvoiceItem\InvoiceItemController;
use App\Http\Controllers\Api\Payment\PaymentController;
use App\Http\Controllers\Api\PaymentMethod\PaymentMethodController;
use App\Http\Controllers\Api\ReceiptController;
use App\Http\Controllers\Api\Scholarship\ScholarshipController;
use App\Http\Controllers\Api\StudentFee\StudentFeeController;
use App\Http\Controllers\Api\StudentScholarship\StudentScholarshipController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {

    // Finance Read + Dashboard/Reports
    Route::middleware('role:SUPER_ADMIN,ADMIN,ACCOUNTANT')->group(function () {
        Route::get('/finance/students', [AccountantFinanceController::class, 'students']);
        Route::get('/finance/dashboard', [AccountantFinanceController::class, 'dashboard']);
        Route::get('/finance/revenue', [AccountantFinanceController::class, 'revenue']);
        Route::get('/finance/outstanding-fees', [AccountantFinanceController::class, 'outstandingFees']);
        Route::get('/finance/reports', [AccountantFinanceController::class, 'reports']);
        Route::get('/finance/reports/payments/export', [AccountantFinanceController::class, 'exportPayments']);
        Route::post('/finance/calculate-discount', [AccountantFinanceController::class, 'calculateDiscount']);

        Route::get('/fee-types', [FeeTypeController::class, 'index']);
        Route::get('/fee-types/{feeType}', [FeeTypeController::class, 'show']);

        Route::get('/student-fees', [StudentFeeController::class, 'index']);
        Route::get('/student-fees/{studentFee}', [StudentFeeController::class, 'show']);

        Route::get('/scholarships', [ScholarshipController::class, 'index']);
        Route::get('/scholarships/{scholarship}', [ScholarshipController::class, 'show']);

        Route::get('/student-scholarships', [StudentScholarshipController::class, 'index']);
        Route::get('/student-scholarships/{studentScholarship}', [StudentScholarshipController::class, 'show']);

        Route::get('/invoices', [InvoiceController::class, 'index']);
        Route::get('/invoices/{invoice}', [InvoiceController::class, 'show']);

        Route::get('/invoice-items', [InvoiceItemController::class, 'index']);
        Route::get('/invoice-items/{invoiceItem}', [InvoiceItemController::class, 'show']);

        Route::get('/payment-methods', [PaymentMethodController::class, 'index']);
        Route::get('/payment-methods/{paymentMethod}', [PaymentMethodController::class, 'show']);

        Route::get('/payments', [PaymentController::class, 'index']);
        Route::get('/payments/{payment}', [PaymentController::class, 'show']);

        Route::get('/receipts', [ReceiptController::class, 'index']);
        Route::get('/receipts/{id}', [ReceiptController::class, 'show']);
    });

    // Accountant manages Fee Types + Scholarships
    Route::middleware('role:SUPER_ADMIN,ADMIN,ACCOUNTANT')->group(function () {
        Route::post('/fee-types', [FeeTypeController::class, 'store']);
        Route::put('/fee-types/{feeType}', [FeeTypeController::class, 'update']);
        Route::patch('/fee-types/{feeType}', [FeeTypeController::class, 'update']);

        Route::post('/scholarships', [ScholarshipController::class, 'store']);
        Route::put('/scholarships/{scholarship}', [ScholarshipController::class, 'update']);
        Route::patch('/scholarships/{scholarship}', [ScholarshipController::class, 'update']);

        Route::post('/student-scholarships', [StudentScholarshipController::class, 'store']);
        Route::put('/student-scholarships/{studentScholarship}', [StudentScholarshipController::class, 'update']);
        Route::patch('/student-scholarships/{studentScholarship}', [StudentScholarshipController::class, 'update']);
    });

    // Accountant Finance Transactions
    Route::middleware('role:SUPER_ADMIN,ACCOUNTANT')->group(function () {
        Route::post('/student-fees', [StudentFeeController::class, 'store']);
        Route::put('/student-fees/{studentFee}', [StudentFeeController::class, 'update']);
        Route::patch('/student-fees/{studentFee}', [StudentFeeController::class, 'update']);

        Route::post('/invoices', [InvoiceController::class, 'store']);
        Route::put('/invoices/{invoice}', [InvoiceController::class, 'update']);
        Route::patch('/invoices/{invoice}', [InvoiceController::class, 'update']);

        Route::post('/invoice-items', [InvoiceItemController::class, 'store']);
        Route::put('/invoice-items/{invoiceItem}', [InvoiceItemController::class, 'update']);
        Route::patch('/invoice-items/{invoiceItem}', [InvoiceItemController::class, 'update']);

        Route::post('/payments', [PaymentController::class, 'store']);
        Route::post('/receipts', [ReceiptController::class, 'store']);
    });

    // Admin Master Data Management
    Route::middleware('role:SUPER_ADMIN,ADMIN')->group(function () {
        Route::delete('/fee-types/{feeType}', [FeeTypeController::class, 'destroy']);

        Route::delete('/scholarships/{scholarship}', [ScholarshipController::class, 'destroy']);
        Route::delete('/student-scholarships/{studentScholarship}', [StudentScholarshipController::class, 'destroy']);

        Route::post('/payment-methods', [PaymentMethodController::class, 'store']);
        Route::put('/payment-methods/{paymentMethod}', [PaymentMethodController::class, 'update']);
        Route::patch('/payment-methods/{paymentMethod}', [PaymentMethodController::class, 'update']);
    });

    // Super Admin Dangerous Operations
    Route::middleware('role:SUPER_ADMIN')->group(function () {
        Route::delete('/student-fees/{studentFee}', [StudentFeeController::class, 'destroy']);

        Route::delete('/invoices/{invoice}', [InvoiceController::class, 'destroy']);
        Route::delete('/invoice-items/{invoiceItem}', [InvoiceItemController::class, 'destroy']);

        Route::put('/payments/{payment}', [PaymentController::class, 'update']);
        Route::patch('/payments/{payment}', [PaymentController::class, 'update']);
        Route::delete('/payments/{payment}', [PaymentController::class, 'destroy']);

        Route::delete('/payment-methods/{paymentMethod}', [PaymentMethodController::class, 'destroy']);
    });
});
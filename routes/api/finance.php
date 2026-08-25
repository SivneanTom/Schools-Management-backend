<?php

use App\Http\Controllers\Api\FeeType\FeeTypeController;
use App\Http\Controllers\Api\StudentFee\StudentFeeController;
use App\Http\Controllers\Api\Scholarship\ScholarshipController;
use App\Http\Controllers\Api\StudentScholarship\StudentScholarshipController;
use App\Http\Controllers\Api\Invoice\InvoiceController;
use App\Http\Controllers\Api\InvoiceItem\InvoiceItemController;
use App\Http\Controllers\Api\PaymentMethod\PaymentMethodController;
use App\Http\Controllers\Api\Payment\PaymentController;
use App\Http\Controllers\Api\ReceiptController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {

    // =====================================================
    // FINANCE READ
    // Super Admin / Admin / Accountant
    // =====================================================

    Route::middleware(
        'role:SUPER_ADMIN,ADMIN,ACCOUNTANT'
    )->group(function () {

        // Fee Types
        Route::get('/fee-types', [FeeTypeController::class, 'index']);
        Route::get('/fee-types/{feeType}', [FeeTypeController::class, 'show']);

        // Student Fees
        Route::get('/student-fees', [StudentFeeController::class, 'index']);
        Route::get('/student-fees/{studentFee}', [StudentFeeController::class, 'show']);

        // Scholarships
        Route::get('/scholarships', [ScholarshipController::class, 'index']);
        Route::get('/scholarships/{scholarship}', [ScholarshipController::class, 'show']);

        // Student Scholarships
        Route::get('/student-scholarships', [StudentScholarshipController::class, 'index']);
        Route::get('/student-scholarships/{studentScholarship}', [StudentScholarshipController::class, 'show']);

        // Invoices
        Route::get('/invoices', [InvoiceController::class, 'index']);
        Route::get('/invoices/{invoice}', [InvoiceController::class, 'show']);

        // Invoice Items
        Route::get('/invoice-items', [InvoiceItemController::class, 'index']);
        Route::get('/invoice-items/{invoiceItem}', [InvoiceItemController::class, 'show']);

        // Payment Methods
        Route::get('/payment-methods', [PaymentMethodController::class, 'index']);
        Route::get('/payment-methods/{paymentMethod}', [PaymentMethodController::class, 'show']);

        // Payments
        Route::get('/payments', [PaymentController::class, 'index']);
        Route::get('/payments/{payment}', [PaymentController::class, 'show']);

        // Receipts
        Route::get('/receipts', [ReceiptController::class, 'index']);
        Route::get('/receipts/{id}', [ReceiptController::class, 'show']);
    });

    // =====================================================
    // FEE TYPE MANAGEMENT
    // =====================================================

    Route::middleware(
        'role:SUPER_ADMIN,ADMIN,ACCOUNTANT'
    )->group(function () {
        Route::post('/fee-types', [FeeTypeController::class, 'store']);
        Route::put('/fee-types/{feeType}', [FeeTypeController::class, 'update']);
        Route::patch('/fee-types/{feeType}', [FeeTypeController::class, 'update']);
    });

    Route::middleware(
        'role:SUPER_ADMIN,ADMIN'
    )->delete(
        '/fee-types/{feeType}',
        [FeeTypeController::class, 'destroy']
    );

    // =====================================================
    // STUDENT FEES
    // =====================================================

    Route::middleware(
        'role:SUPER_ADMIN,ACCOUNTANT'
    )->group(function () {
        Route::post('/student-fees', [StudentFeeController::class, 'store']);
        Route::put('/student-fees/{studentFee}', [StudentFeeController::class, 'update']);
        Route::patch('/student-fees/{studentFee}', [StudentFeeController::class, 'update']);
    });

    Route::middleware(
        'role:SUPER_ADMIN'
    )->delete(
        '/student-fees/{studentFee}',
        [StudentFeeController::class, 'destroy']
    );

    // =====================================================
    // SCHOLARSHIPS
    // Admin + Accountant manage
    // =====================================================

    Route::middleware(
        'role:SUPER_ADMIN,ADMIN,ACCOUNTANT'
    )->group(function () {
        Route::post('/scholarships', [ScholarshipController::class, 'store']);
        Route::put('/scholarships/{scholarship}', [ScholarshipController::class, 'update']);
        Route::patch('/scholarships/{scholarship}', [ScholarshipController::class, 'update']);

        Route::post('/student-scholarships', [StudentScholarshipController::class, 'store']);
        Route::put('/student-scholarships/{studentScholarship}', [StudentScholarshipController::class, 'update']);
        Route::patch('/student-scholarships/{studentScholarship}', [StudentScholarshipController::class, 'update']);
    });

    Route::middleware(
        'role:SUPER_ADMIN,ADMIN'
    )->group(function () {
        Route::delete('/scholarships/{scholarship}', [ScholarshipController::class, 'destroy']);
        Route::delete('/student-scholarships/{studentScholarship}', [StudentScholarshipController::class, 'destroy']);
    });

    // =====================================================
    // INVOICES
    // Accountant manages financial transaction data
    // =====================================================

    Route::middleware(
        'role:SUPER_ADMIN,ACCOUNTANT'
    )->group(function () {
        Route::post('/invoices', [InvoiceController::class, 'store']);
        Route::put('/invoices/{invoice}', [InvoiceController::class, 'update']);
        Route::patch('/invoices/{invoice}', [InvoiceController::class, 'update']);

        Route::post('/invoice-items', [InvoiceItemController::class, 'store']);
        Route::put('/invoice-items/{invoiceItem}', [InvoiceItemController::class, 'update']);
        Route::patch('/invoice-items/{invoiceItem}', [InvoiceItemController::class, 'update']);

        Route::post('/payments', [PaymentController::class, 'store']);
        Route::put('/payments/{payment}', [PaymentController::class, 'update']);
        Route::patch('/payments/{payment}', [PaymentController::class, 'update']);

        Route::post('/receipts', [ReceiptController::class, 'store']);
    });

    Route::middleware(
        'role:SUPER_ADMIN'
    )->group(function () {
        Route::delete('/invoices/{invoice}', [InvoiceController::class, 'destroy']);
        Route::delete('/invoice-items/{invoiceItem}', [InvoiceItemController::class, 'destroy']);
        Route::delete('/payments/{payment}', [PaymentController::class, 'destroy']);
    });

    // =====================================================
    // PAYMENT METHODS
    // Seeded master data
    // =====================================================

    Route::middleware(
        'role:SUPER_ADMIN,ADMIN,ACCOUNTANT'
    )->group(function () {
        Route::post('/payment-methods', [PaymentMethodController::class, 'store']);
        Route::put('/payment-methods/{paymentMethod}', [PaymentMethodController::class, 'update']);
        Route::patch('/payment-methods/{paymentMethod}', [PaymentMethodController::class, 'update']);
    });

    Route::middleware(
        'role:SUPER_ADMIN'
    )->delete(
        '/payment-methods/{paymentMethod}',
        [PaymentMethodController::class, 'destroy']
    );
});

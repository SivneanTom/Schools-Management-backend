<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->restrictOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->restrictOnDelete();
            $table->string('invoice_no')->unique();
            $table->date('issued_date');
            $table->date('due_date')->nullable();
            $table->decimal('subtotal',12,2)->default(0);
            $table->decimal('discount_total',12,2)->default(0);
            $table->decimal('total_amount',12,2)->default(0);
            $table->string('status',30)->default('UNPAID');
            $table->timestamps();
            $table->index(['student_id','status']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('invoices');
    }
};

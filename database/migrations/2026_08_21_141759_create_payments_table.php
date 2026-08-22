<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{

    public function up(): void
    {

        Schema::create('payments', function (Blueprint $table) {

            $table->id();
            $table->foreignId('invoice_id')
                ->constrained('invoices')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('payment_method_id')
                ->constrained('payment_methods')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('received_by_staff_id')
                ->nullable()
                ->constrained('staff')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->string('payment_no')
                ->unique();

            $table->decimal('amount',12,2);

            $table->dateTime('paid_at');

            $table->string('reference_no')
                ->nullable();

            $table->string('status')
                ->default('COMPLETED');
            $table->timestamps();

        });

    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }

};
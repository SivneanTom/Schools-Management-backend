<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name_km', 255);
            $table->string('name_en', 255)->nullable();
            $table->text('description_km')->nullable();
            $table->text('description_en')->nullable();
            $table->decimal('default_amount', 12, 2)->default(0);
            $table->string('frequency', 30)->default('ONE_TIME');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'frequency']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_types');
    }
};

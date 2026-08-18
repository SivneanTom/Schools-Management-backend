<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name_km', 150);
            $table->string('name_en', 150);
            $table->text('description_km')->nullable();
            $table->text('description_en')->nullable();
            $table->unsignedInteger('credit_hours');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name_km', 150);
            $table->string('name_en', 150)->nullable();
            $table->string('building_km', 150)->nullable();
            $table->string('building_en', 150)->nullable();
            $table->unsignedInteger('capacity')->nullable();
            $table->string('status', 20)->default('ACTIVE');
            $table->timestamps();

            $table->index('status');
            $table->index('name_km');
            $table->index('name_en');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('warehouses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('store_id')->nullable();
            $table->uuid('convection_id')->nullable();
            $table->string('name');
            $table->string('address');
            $table->string('phone_number');
            $table->enum('status', ['active', 'suspend'])->default('active');
            $table->timestamps();

            $table->foreign('store_id')->references('id')->on('stores')->onDelete('set null');
            $table->foreign('convection_id')->references('id')->on('convections')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouses');
    }
};
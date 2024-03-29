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
        Schema::create('commisions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('no_commision');
            $table->uuid('nama_bank')->default(null);
            $table->uuid('no_rekening')->default(null);
            $table->uuid('nama_rekening')->default(null);
            $table->string('ref_dokumen_id');
            $table->uuid('broker');
            $table->unsignedBigInteger('broker_fee');
            $table->unsignedBigInteger('payment')->default(0);
            $table->enum('paid_status', ['unpaid', 'partialy_paid', 'paid'])->default('unpaid');
            $table->timestamps();

            $table->foreign('broker')->references('id')->on('contacts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commisions');
    }
};

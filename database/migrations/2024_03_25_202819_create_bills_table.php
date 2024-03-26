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
        Schema::create('bills', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('purchase_id');
            $table->uuid('bank_id')->nullable()->default(null);
            $table->uuid('contact_id');
            $table->uuid('warehouse_id');
            $table->string('sku');
            $table->unsignedBigInteger('ketebalan');
            $table->unsignedBigInteger('setting');
            $table->unsignedBigInteger('gramasi');
            $table->unsignedBigInteger('stock_roll');
            $table->unsignedBigInteger('stock_kg');
            $table->unsignedBigInteger('stock_rib');
            $table->unsignedBigInteger('bill_price')->default(0);
            $table->unsignedBigInteger('payment')->default(0);
            $table->enum('paid_status', ['unpaid','partialy_paid', 'paid'])->default('unpaid');
            $table->timestamps();

            $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('cascade');
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
            $table->foreign('bank_id')->references('id')->on('banks')->onDelete('cascade');
            $table->foreign('purchase_id')->references('id')->on('purchase_orders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};

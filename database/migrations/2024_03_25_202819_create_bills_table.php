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
            $table->string('no_bill');
            $table->uuid('purchase_id');
            $table->string('nama_bank')->default(null);
            $table->string('no_rekening')->default(null);
            $table->string('nama_rekening')->default(null);
            $table->uuid('contact_id');
            $table->uuid('warehouse_id');
            $table->string('sku');
            $table->string('nama_barang');
            $table->string('ketebalan');
            $table->string('setting');
            $table->string('gramasi');
            $table->decimal('stock_roll', 10, 2);
            $table->decimal('stock_kg', 10, 2);
            $table->decimal('stock_rib', 10, 2);
            $table->decimal('stock_roll_rev', 10, 2)->default(0);
            $table->decimal('stock_kg_rev', 10, 2)->default(0);
            $table->decimal('stock_rib_rev', 10, 2)->default(0);
            $table->unsignedBigInteger('bill_price');
            $table->unsignedBigInteger('payment')->default(0);
            $table->enum('paid_status', ['unpaid', 'partialy_paid', 'paid'])->default('unpaid');
            $table->timestamps();

            $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('cascade');
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
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

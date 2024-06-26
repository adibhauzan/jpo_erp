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
        Schema::create('invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('no_invoice');
            $table->uuid('sales_order_id');
            $table->uuid('warehouse_id');
            $table->uuid('contact_id');
            $table->uuid('bank_id')->nullable();
            $table->string('sku');
            $table->string('nama_barang');
            $table->unsignedBigInteger('sell_price');
            $table->string('ketebalan');
            $table->string('setting');
            $table->string('gramasi');
            $table->decimal('stock_roll', 10, 2);
            $table->decimal('stock_kg', 10, 2);
            $table->decimal('stock_rib', 10, 2);
            $table->unsignedBigInteger('payment')->default(0);
            $table->boolean('is_broker')->default(0);
            $table->uuid('broker')->nullable();
            $table->unsignedBigInteger('broker_fee')->nullable();
            $table->enum('paid_status', ['unpaid', 'partialy_paid', 'paid'])->default('unpaid');
            $table->timestamps();


            $table->foreign('sales_order_id')->references('id')->on('sales_orders')->onDelete('cascade');
            $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('cascade');
            $table->foreign('broker')->references('id')->on('contacts')->onDelete('cascade');
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
            $table->foreign('bank_id')->references('id')->on('banks')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};

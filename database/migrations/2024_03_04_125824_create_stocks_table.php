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
        Schema::create('stocks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('warehouse_id');
            $table->uuid('po_id');
            $table->string('nama_barang');
            $table->string('grade');
            $table->string('sku');
            $table->string('description');
            $table->string('attachment_image');
            $table->string('ketebalan');
            $table->string('setting');
            $table->string('gramasi');
            $table->unsignedBigInteger('price');
            $table->decimal('stock_roll', 10, 2);
            $table->decimal('stock_kg', 10, 2);
            $table->decimal('stock_rib', 10, 2);
            $table->date('date_received')->nullable();

            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');

            $table->foreign('po_id')->references('id')->on('purchase_orders')->onDelete('cascade');


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};

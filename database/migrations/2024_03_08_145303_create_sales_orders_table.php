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
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('status', ['draft', 'received', 'done'])->default('draft');
            $table->uuid('contact_id');
            $table->uuid('warehouse_id');
            $table->string('no_so');
            $table->string('no_do');
            $table->date('date');
            $table->uuid('broker')->nullable();
            $table->unsignedBigInteger('broker_fee')->nullable();
            $table->string('sku');
            $table->string('nama_barang');
            $table->string('grade');
            $table->string('description');
            $table->string('attachment_image');
            $table->unsignedBigInteger('ketebalan');
            $table->unsignedBigInteger('setting');
            $table->unsignedBigInteger('gramasi');
            $table->unsignedBigInteger('price');
            $table->unsignedBigInteger('stock_roll');
            $table->unsignedBigInteger('stock_kg');
            $table->unsignedBigInteger('stock_rib');
            $table->unsignedBigInteger('stock_roll_rev');
            $table->unsignedBigInteger('stock_kg_rev');
            $table->unsignedBigInteger('stock_rib_rev');
            $table->date('date_received')->nullable();
            $table->timestamps();

            $table->foreign('broker')->references('id')->on('contacts')->onDelete('cascade');
            $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('cascade');
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_orders');
    }
};
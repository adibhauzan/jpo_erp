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
            $table->string('ketebalan');
            $table->string('setting');
            $table->string('gramasi');
            $table->unsignedBigInteger('price');
            $table->decimal('stock_roll', 10, 2);
            $table->decimal('stock_kg', 10, 2);
            $table->decimal('stock_rib', 10, 2);
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

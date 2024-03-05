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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('status', ['draft', 'received', 'done'])->default('draft');
            $table->uuid('contact_id');
            $table->uuid('warehouse_id');
            $table->string('no_po');
            $table->string('no_do');
            $table->date('date');
            $table->string('nama_barang');
            $table->string('grade');
            $table->string('sku');
            $table->string('description');
            $table->string('attachment_image');
            $table->unsignedBigInteger('ketebalan');
            $table->unsignedBigInteger('setting');
            $table->unsignedBigInteger('gramasi');
            $table->unsignedBigInteger('stock');
            $table->unsignedBigInteger('price');
            $table->unsignedBigInteger('stock_rib');

            $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('cascade');
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
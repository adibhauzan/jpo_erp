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
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('stock_roll_rev')->default(0)->after('stock_rib');
            $table->unsignedBigInteger('stock_kg_rev')->default(0)->after('stock_roll_rev');
            $table->unsignedBigInteger('stock_rib_rev')->default(0)->after('stock_kg_rev');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropColumn('stock_roll_rev');
            $table->dropColumn('stock_kg_rev');
            $table->dropColumn('stock_rib_rev');
        });
    }
};

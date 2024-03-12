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
        Schema::create('bans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('bannable_id')->nullable();
            $table->string('bannable_type')->nullable();
            $table->uuid('created_by_id')->nullable(); // Mengubah tipe data menjadi uuid
            $table->string('created_by_type')->nullable();
            $table->text('comment')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('bannable_id');
            $table->index('created_by_id'); // Menambahkan index untuk created_by_id
            $table->index('ip');
            $table->index('expired_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('ban.table'));
    }
};
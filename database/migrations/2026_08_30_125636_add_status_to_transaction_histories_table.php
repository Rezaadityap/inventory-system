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
        Schema::table('transaction_histories', function (Blueprint $table) {
            $table->enum('status', ['Menunggu', 'Disetujui', 'Diproses', 'Dikirim', 'Selesai', 'Dibatalkan'])->change()->default('Menunggu');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaction_histories', function (Blueprint $table) {
            $table->enum('status', ['Menunggu', 'Disetujui', 'Dikirim', 'Selesai', 'Dibatalkan'])->change()->default('Menunggu');
        });
    }
};

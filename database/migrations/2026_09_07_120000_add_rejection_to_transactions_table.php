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
        Schema::table('transactions', function (Blueprint $table) {
            $table->enum('status', ['Menunggu', 'Disetujui', 'Ditolak', 'Diproses', 'Dikirim', 'Selesai', 'Dibatalkan'])->change()->default('Menunggu');
            $table->text('admin_note')->nullable()->after('note');
            $table->timestamp('rejected_at')->nullable()->after('cancelled_at');
        });

        Schema::table('transaction_histories', function (Blueprint $table) {
            $table->enum('status', ['Menunggu', 'Disetujui', 'Ditolak', 'Diproses', 'Dikirim', 'Selesai', 'Dibatalkan'])->change()->default('Menunggu');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaction_histories', function (Blueprint $table) {
            $table->enum('status', ['Menunggu', 'Disetujui', 'Diproses', 'Dikirim', 'Selesai', 'Dibatalkan'])->change()->default('Menunggu');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['admin_note', 'rejected_at']);
            $table->enum('status', ['Menunggu', 'Disetujui', 'Diproses', 'Dikirim', 'Selesai', 'Dibatalkan'])->change()->default('Menunggu');
        });
    }
};

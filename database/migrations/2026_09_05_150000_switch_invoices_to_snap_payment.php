<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pindah dari menyimpan nomor Virtual Account ke token Snap.
     *
     * Bank tidak lagi dipilih di aplikasi -- pelanggan memilihnya di dalam Snap,
     * jadi `va_bank`/`va_number` tidak punya tempat lagi.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('snap_token')->nullable()->after('paid_at');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropUnique(['va_number']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['va_bank', 'va_number']);
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('va_bank')->nullable()->after('paid_at');
            $table->string('va_number')->nullable()->unique()->after('va_bank');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('snap_token');
        });
    }
};

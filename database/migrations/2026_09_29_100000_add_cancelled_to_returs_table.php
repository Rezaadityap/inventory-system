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
        Schema::table('returs', function (Blueprint $table) {
            $table->enum('status', ['Menunggu', 'Disetujui', 'Ditolak', 'Selesai', 'Dibatalkan'])->default('Menunggu')->change();
            $table->timestamp('cancelled_at')->nullable()->after('completed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('returs', function (Blueprint $table) {
            $table->dropColumn('cancelled_at');
            $table->enum('status', ['Menunggu', 'Disetujui', 'Ditolak', 'Selesai'])->default('Menunggu')->change();
        });
    }
};

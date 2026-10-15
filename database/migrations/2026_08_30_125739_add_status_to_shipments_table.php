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
        Schema::table('shipments', function (Blueprint $table) {
            $table->enum('status', ['Diproses', 'Dikirim', 'Sampai', 'Diterima'])->change()->default('Diproses');
            $table->timestamp('planned_shipped_at')->nullable()->after('shipped_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->enum('status', ['Dikirim', 'Sampai', 'Diterima'])->change()->default('Dikirim');
            $table->dropColumn('planned_shipped_at');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('retur_items', function (Blueprint $table) {
            $table->unsignedBigInteger('price')->default(0)->after('qty');
        });

        DB::statement('
            UPDATE retur_items ri
            JOIN transaction_items ti ON ti.id = ri.transaction_item_id
            SET ri.price = ROUND(ti.price / NULLIF(ti.qty, 0) * ri.qty)
            WHERE ti.qty > 0
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('retur_items', function (Blueprint $table) {
            $table->dropColumn('price');
        });
    }
};

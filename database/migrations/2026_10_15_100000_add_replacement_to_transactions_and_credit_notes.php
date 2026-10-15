<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('credit_notes', function (Blueprint $table) {
            $table->enum('status', ['open', 'applied', 'refunded', 'replaced'])->default('open')->change();
            $table->timestamp('replaced_at')->nullable()->after('refunded_at');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('credit_note_id')->nullable()->after('customer_id')->constrained('credit_notes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('credit_note_id');
        });

        Schema::table('credit_notes', function (Blueprint $table) {
            $table->dropColumn('replaced_at');
            $table->enum('status', ['open', 'applied', 'refunded'])->default('open')->change();
        });
    }
};

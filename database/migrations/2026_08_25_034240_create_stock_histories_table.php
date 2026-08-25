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
        Schema::create('stock_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('waste_id')->constrained('wastes')->cascadeOnDelete();
            $table->enum('type', ['Masuk', 'Keluar'])->default('Masuk');
            $table->enum('source', ['Order', 'Retur', 'Manual'])->default('Manual');
            $table->integer('qty');
            $table->integer('qty_before');
            $table->integer('qty_after');
            $table->text('remark')->nullable();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_histories');
    }
};

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
        Schema::create('retur_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('retur_id')->constrained('returs')->cascadeOnDelete();
            $table->foreignId('transaction_item_id')->constrained('transaction_items')->cascadeOnDelete();
            $table->foreignId('waste_id')->constrained('wastes')->cascadeOnDelete();
            $table->integer('qty');
            $table->text('note_item')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retur_items');
    }
};

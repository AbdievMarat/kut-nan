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
        Schema::create('ingredient_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ingredient_id')->constrained('ingredients')->cascadeOnUpdate()->cascadeOnDelete();
            $table->date('date');
            $table->decimal('income', 10, 2)->default(0);
            $table->decimal('usage', 10, 2)->default(0);
            $table->decimal('usage_missing', 10, 2)->default(0);
            $table->decimal('usage_taken_from_stock', 10, 2)->default(0);
            $table->decimal('usage_kitchen', 10, 2)->default(0);
            $table->decimal('stock', 10, 2)->default(0);
            $table->timestamps();

            $table->unique(['ingredient_id', 'date'], 'unique_ingredient_usage');
            $table->index(['ingredient_id', 'date'], 'index_ingredient_usage_ingredient_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ingredient_usages');
    }
};

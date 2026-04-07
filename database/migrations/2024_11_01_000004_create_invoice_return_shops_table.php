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
        Schema::create('invoice_return_shops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_return_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('shop');
            $table->integer('amount');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_return_shops');
    }
};

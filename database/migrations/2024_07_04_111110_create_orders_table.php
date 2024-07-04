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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bus_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->date('date');
            $table->integer('product_1')->nullable()->comment('Москва');
            $table->integer('product_2')->nullable()->comment('Москва уп');
            $table->integer('product_3')->nullable()->comment('Солдат');
            $table->integer('product_4')->nullable()->comment('Отруб');
            $table->integer('product_5')->nullable()->comment('Налив');
            $table->integer('product_6')->nullable()->comment('Тостер');
            $table->integer('product_7')->nullable()->comment('Тостер кара');
            $table->integer('product_8')->nullable()->comment('Мини тостер');
            $table->integer('product_9')->nullable()->comment('Гречневый');
            $table->integer('product_10')->nullable()->comment('Зерновой');
            $table->integer('product_11')->nullable()->comment('Багет');
            $table->integer('product_12')->nullable()->comment('Без дрожж');
            $table->integer('product_13')->nullable()->comment('Чемпион');
            $table->integer('product_14')->nullable()->comment('Абсолют');
            $table->integer('product_15')->nullable()->comment('Кукурузный');
            $table->integer('product_16')->nullable()->comment('Уп. Бород');
            $table->integer('product_17')->nullable()->comment('Уп. Батон отруб');
            $table->integer('product_18')->nullable()->comment('Уп. Батон серый');
            $table->integer('product_19')->nullable()->comment('Уп. Батон белый');
            $table->integer('product_20')->nullable()->comment('Баатыр');
            $table->integer('product_21')->nullable()->comment('Обама отруб');
            $table->integer('product_22')->nullable()->comment('Обама ржан');
            $table->integer('product_23')->nullable()->comment('Обама серый');
            $table->integer('product_24')->nullable()->comment('Уп. Моск');
            $table->integer('product_25')->nullable()->comment('Гамбургер');
            $table->integer('product_26')->nullable()->comment('Тартин');
            $table->integer('product_27')->nullable()->comment('Тартин зерновой');
            $table->integer('product_28')->nullable()->comment('Тартин ржаной');
            $table->integer('product_29')->nullable()->comment('Тартин с луком');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

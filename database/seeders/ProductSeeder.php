<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            ['name' => 'Москва', 'price' => 21, 'sort' => 1],
            ['name' => 'Москва уп', 'price' => 23, 'sort' => 2],
            ['name' => 'Солдат', 'price' => 21, 'sort' => 3],
            ['name' => 'Отруб', 'price' => 21, 'sort' => 4],
            ['name' => 'Налив', 'price' => 20, 'sort' => 5],
            ['name' => 'Тостер', 'price' => 33, 'sort' => 6],
            ['name' => 'Тостер кара', 'price' => 33, 'sort' => 7],
            ['name' => 'Мини тостер', 'price' => 18, 'sort' => 8],
            ['name' => 'Гречневый', 'price' => 30, 'sort' => 9],
            ['name' => 'Зерновой', 'price' => 30, 'sort' => 10],
            ['name' => 'Багет', 'price' => 30, 'sort' => 11],
            ['name' => 'Без дрожж', 'price' => 111, 'sort' => 12],
            ['name' => 'Чемпион', 'price' => 30, 'sort' => 13],
            ['name' => 'Абсолют', 'price' => 30, 'sort' => 14],
            ['name' => 'Кукурузный', 'price' => 30, 'sort' => 15],
            ['name' => 'Уп. Бород', 'price' => 24, 'sort' => 16],
            ['name' => 'Уп. Батон отруб', 'price' => 24, 'sort' => 17],
            ['name' => 'Уп. Батон серый', 'price' => 24, 'sort' => 18],
            ['name' => 'Уп. Батон белый', 'price' => 24, 'sort' => 19],
            ['name' => 'Баатыр', 'price' => 25, 'sort' => 20],
            ['name' => 'Обама отруб', 'price' => 25, 'sort' => 21],
            ['name' => 'Обама ржан', 'price' => 25, 'sort' => 22],
            ['name' => 'Обама серый', 'price' => 25, 'sort' => 23],
            ['name' => 'Уп. Моск', 'price' => 23, 'sort' => 24],
            ['name' => 'Гамбургер', 'price' => 10, 'sort' => 25],
            ['name' => 'Тартин', 'price' => 50, 'sort' => 26],
            ['name' => 'Тартин зерновой', 'price' => 50, 'sort' => 27],
            ['name' => 'Тартин ржаной', 'price' => 50, 'sort' => 28],
            ['name' => 'Тартин с луком', 'price' => 50, 'sort' => 29],
        ];

        foreach ($products as $product) {
            Product::factory()->state($product)->create();
        }
    }
}
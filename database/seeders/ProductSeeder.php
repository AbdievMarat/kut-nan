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
            ['name' => 'Московский', 'sort' => 1, 'pieces_per_cart' => 216, 'is_active' => Product::IS_ACTIVE, 'is_in_report' => Product::IS_IN_REPORT],
            ['name' => 'Москва уп', 'sort' => 2, 'pieces_per_cart' => 1, 'is_active' => Product::IS_ACTIVE, 'is_in_report' => Product::IS_IN_REPORT],
            ['name' => 'Солдатский', 'sort' => 3, 'pieces_per_cart' => 216, 'is_active' => Product::IS_ACTIVE, 'is_in_report' => Product::IS_IN_REPORT],
            ['name' => 'Отрубной', 'sort' => 4, 'pieces_per_cart' => 216, 'is_active' => Product::IS_ACTIVE, 'is_in_report' => Product::IS_IN_REPORT],
            ['name' => 'Наливной', 'sort' => 5, 'pieces_per_cart' => 216, 'is_active' => Product::IS_ACTIVE, 'is_in_report' => Product::IS_IN_REPORT],
            ['name' => 'Тостер', 'sort' => 6, 'pieces_per_cart' => 108, 'is_active' => Product::IS_ACTIVE, 'is_in_report' => Product::IS_IN_REPORT],
            ['name' => 'Тостер кара', 'sort' => 7, 'pieces_per_cart' => 108, 'is_active' => Product::IS_ACTIVE, 'is_in_report' => Product::IS_IN_REPORT],
            ['name' => 'Мини тостер', 'sort' => 8, 'pieces_per_cart' => 200, 'is_active' => Product::IS_ACTIVE, 'is_in_report' => Product::IS_IN_REPORT],
            ['name' => 'Гречневый', 'sort' => 9, 'pieces_per_cart' => 132, 'is_active' => Product::IS_ACTIVE, 'is_in_report' => Product::IS_IN_REPORT],
            ['name' => 'Зерновой', 'sort' => 10, 'pieces_per_cart' => 144, 'is_active' => Product::IS_ACTIVE, 'is_in_report' => Product::IS_IN_REPORT],
            ['name' => 'Багет', 'sort' => 11, 'pieces_per_cart' => 132, 'is_active' => Product::IS_ACTIVE, 'is_in_report' => Product::IS_IN_REPORT],
            ['name' => 'Без дрожжевой', 'sort' => 12, 'pieces_per_cart' => 120, 'is_active' => Product::IS_ACTIVE, 'is_in_report' => Product::IS_IN_REPORT],
            ['name' => 'Чемпион', 'sort' => 13, 'pieces_per_cart' => 120, 'is_active' => Product::IS_ACTIVE, 'is_in_report' => Product::IS_IN_REPORT],
            ['name' => 'Абсолют', 'sort' => 14, 'pieces_per_cart' => 132, 'is_active' => Product::IS_ACTIVE, 'is_in_report' => Product::IS_IN_REPORT],
            ['name' => 'Кукурузный', 'sort' => 15, 'pieces_per_cart' => 132, 'is_active' => Product::IS_ACTIVE, 'is_in_report' => Product::IS_IN_REPORT],
            ['name' => 'Бородинский', 'sort' => 16, 'pieces_per_cart' => 216, 'is_active' => Product::IS_ACTIVE, 'is_in_report' => Product::IS_IN_REPORT],
            ['name' => 'Батон отруб', 'sort' => 17, 'pieces_per_cart' => 132, 'is_active' => Product::IS_ACTIVE, 'is_in_report' => Product::IS_IN_REPORT],
            ['name' => 'Батон серый', 'sort' => 18, 'pieces_per_cart' => 132, 'is_active' => Product::IS_ACTIVE, 'is_in_report' => Product::IS_IN_REPORT],
            ['name' => 'Батон белый', 'sort' => 19, 'pieces_per_cart' => 132, 'is_active' => Product::IS_ACTIVE, 'is_in_report' => Product::IS_IN_REPORT],
            ['name' => 'Баатыр', 'sort' => 20, 'pieces_per_cart' => 216, 'is_active' => Product::IS_ACTIVE, 'is_in_report' => Product::IS_IN_REPORT],
            ['name' => 'Обама отруб', 'sort' => 21, 'pieces_per_cart' => 176, 'is_active' => Product::IS_ACTIVE, 'is_in_report' => Product::IS_IN_REPORT],
            ['name' => 'Обама ржан', 'sort' => 22, 'pieces_per_cart' => 176, 'is_active' => Product::IS_ACTIVE, 'is_in_report' => Product::IS_IN_REPORT],
            ['name' => 'Обама серый', 'sort' => 23, 'pieces_per_cart' => 176, 'is_active' => Product::IS_ACTIVE, 'is_in_report' => Product::IS_IN_REPORT],
            ['name' => 'Уп. Моск', 'sort' => 24, 'pieces_per_cart' => 1, 'is_active' => Product::IS_ACTIVE, 'is_in_report' => Product::IS_IN_REPORT],
            ['name' => 'Гамбургер', 'sort' => 25, 'pieces_per_cart' => 420, 'is_active' => Product::IS_ACTIVE, 'is_in_report' => Product::IS_IN_REPORT],
            ['name' => 'Токоч', 'sort' => 26, 'pieces_per_cart' => 108, 'is_active' => Product::IS_ACTIVE, 'is_in_report' => Product::IS_IN_REPORT],
            ['name' => 'Тартин', 'sort' => 27, 'pieces_per_cart' => 50, 'is_active' => Product::IS_ACTIVE, 'is_in_report' => Product::IS_IN_REPORT],
            ['name' => 'Тартин зерновой', 'sort' => 28, 'pieces_per_cart' => 50, 'is_active' => Product::IS_ACTIVE, 'is_in_report' => Product::IS_IN_REPORT],
            ['name' => 'Тартин ржаной', 'sort' => 29, 'pieces_per_cart' => 50, 'is_active' => Product::IS_ACTIVE, 'is_in_report' => Product::IS_IN_REPORT],
            ['name' => 'Тартин с луком', 'sort' => 30, 'pieces_per_cart' => 50, 'is_active' => Product::IS_ACTIVE, 'is_in_report' => Product::IS_IN_REPORT],
            ['name' => 'Маленькая лепешка', 'sort' => 31, 'pieces_per_cart' => 1, 'is_active' => Product::IS_ACTIVE, 'is_in_report' => Product::IS_NOT_IN_REPORT],
        ];

        foreach ($products as $product) {
            Product::factory()->state($product)->create();
        }
    }
}

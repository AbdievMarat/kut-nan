<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use Illuminate\Database\Seeder;

class IngredientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ingredients = [
            ['name' => 'I сорт муки', 'unit' => 'мешок (50 кг)', 'sort' => 1],
            ['name' => 'II сорт муки', 'unit' => 'мешок (50 кг)', 'sort' => 2],
            ['name' => 'Ржаная мука', 'unit' => 'кг', 'sort' => 3],
            ['name' => 'Дрожжи', 'unit' => 'кг', 'sort' => 4],
            ['name' => 'Соль', 'unit' => 'кг', 'sort' => 5],
            ['name' => 'Масло растительное', 'unit' => 'л', 'sort' => 6],
            ['name' => 'Колер', 'unit' => 'л', 'sort' => 7],
            ['name' => 'Жир', 'unit' => 'мл', 'sort' => 8],
            ['name' => 'Кукурузная мука', 'unit' => 'кг', 'sort' => 9],
            ['name' => 'Гречневая мука', 'unit' => 'кг', 'sort' => 10],
            ['name' => 'Зерновая мука', 'unit' => 'кг', 'sort' => 11],
            ['name' => 'Чемпион мука', 'unit' => 'кг', 'sort' => 12],
            ['name' => 'Бездрожжевая смесь', 'unit' => 'кг', 'sort' => 13],
        ];

        foreach ($ingredients as $ingredient) {
            Ingredient::factory()->state($ingredient)->create();
        }
    }
}

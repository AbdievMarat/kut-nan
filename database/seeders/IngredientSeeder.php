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
            ['name' => 'I сорт муки', 'short_name'=> 'I', 'unit' => 'мешок (50 кг)', 'sort' => 1],
            ['name' => 'II сорт муки', 'short_name'=> 'II', 'unit' => 'мешок (50 кг)', 'sort' => 2],
            ['name' => 'Ржаная мука', 'short_name'=> 'Рж', 'unit' => 'кг', 'sort' => 3],
            ['name' => 'Дрожжи', 'short_name'=> 'Др', 'unit' => 'кг', 'sort' => 4],
            ['name' => 'Соль', 'short_name'=> 'Соль', 'unit' => 'кг', 'sort' => 5],
            ['name' => 'Масло растительное', 'short_name'=> 'Масло', 'unit' => 'л', 'sort' => 6],
            ['name' => 'Колер', 'short_name'=> 'Колер', 'unit' => 'л', 'sort' => 7],
            ['name' => 'Жир', 'short_name'=> 'Жир', 'unit' => 'мл', 'sort' => 8],
            ['name' => 'Кукурузная мука', 'short_name'=> 'Кукур', 'unit' => 'кг', 'sort' => 9],
            ['name' => 'Гречневая мука', 'short_name'=> 'Гр', 'unit' => 'кг', 'sort' => 10],
            ['name' => 'Зерновая мука', 'short_name'=> 'Зер', 'unit' => 'кг', 'sort' => 11],
            ['name' => 'Чемпион мука', 'short_name'=> 'Чемп', 'unit' => 'кг', 'sort' => 12],
            ['name' => 'Бездрожжевая смесь', 'short_name'=> 'Бездр', 'unit' => 'кг', 'sort' => 13],
        ];

        foreach ($ingredients as $ingredient) {
            Ingredient::factory()->state($ingredient)->create();
        }
    }
}

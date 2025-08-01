<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(UserSeeder::class);
        $this->call(BusSeeder::class);
        $this->call(ProductSeeder::class);
        $this->call(IngredientSeeder::class);
    }
}

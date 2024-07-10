<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Марат',
            'email' => 'abdiev.m.t@gmail.com',
        ]);

        User::factory()->create([
            'name' => 'Администратор',
            'email' => 'admin@kutnan.kg',
        ]);
    }
}

<?php

namespace Database\Seeders;

use App\Models\Bus;
use Illuminate\Database\Seeder;

class BusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $buses = [
            ['license_plate' => 5164, 'serial_number' => '№ 1', 'sort' => 1],
            ['license_plate' => 5324, 'serial_number' => '№ 2', 'sort' => 2],
            ['license_plate' => 446, 'serial_number' => '№ 3', 'sort' => 3],
            ['license_plate' => 468, 'serial_number' => '№ 4', 'sort' => 4],
            ['license_plate' => 724, 'serial_number' => '№ 5', 'sort' => 5],
            ['license_plate' => 5492, 'serial_number' => '№ 6', 'sort' => 6],
            ['license_plate' => 863, 'serial_number' => '№ 7', 'sort' => 7],
            ['license_plate' => 8206, 'serial_number' => '№ 8', 'sort' => 8],
            ['license_plate' => 205, 'serial_number' => '№ 9', 'sort' => 9],
            ['license_plate' => 4052, 'serial_number' => '№ 10', 'sort' => 10],
            ['license_plate' => 421, 'serial_number' => '№ 12', 'sort' => 11],
            ['license_plate' => 9547, 'serial_number' => '№ 13', 'sort' => 13],
            ['license_plate' => 7104, 'serial_number' => '№ 14', 'sort' => 14],
            ['license_plate' => 290, 'serial_number' => '№ 15', 'sort' => 15],
            ['license_plate' => 352, 'serial_number' => '№ 16', 'sort' => 16],
            ['license_plate' => 9780, 'serial_number' => '№ 17', 'sort' => 17],
            ['license_plate' => 0246, 'serial_number' => '№ 18', 'sort' => 18],
            ['license_plate' => 2165, 'serial_number' => '№ 19', 'sort' => 19],
            ['license_plate' => 403, 'serial_number' => '№ 20', 'sort' => 20],
            ['license_plate' => 4724, 'serial_number' => '№ 21', 'sort' => 21],
            ['license_plate' => 310, 'serial_number' => '№ 23', 'sort' => 22],
            ['license_plate' => 100, 'serial_number' => 'НАРОД', 'sort' => 23],
            ['license_plate' => 200, 'serial_number' => 'АЗИЯ', 'sort' => 24],
            ['license_plate' => 300, 'serial_number' => 'КОЛ', 'sort' => 25],
        ];

        foreach ($buses as $bus) {
            Bus::factory()->state($bus)->create();
        }
    }
}

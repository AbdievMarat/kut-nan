<?php

namespace Database\Factories;

use App\Models\Bus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'bus_id' => Bus::all()->random(),
            'date' => $this->faker->dateTimeBetween('now', '+1 year')->format('Y-m-d'),
            'product_1' => $this->faker->optional()->numberBetween(1, 15),
            'product_2' => $this->faker->optional()->numberBetween(1, 15),
            'product_3' => $this->faker->optional()->numberBetween(1, 15),
            'product_4' => $this->faker->optional()->numberBetween(1, 15),
            'product_5' => $this->faker->optional()->numberBetween(1, 15),
            'product_6' => $this->faker->optional()->numberBetween(1, 15),
            'product_7' => $this->faker->optional()->numberBetween(1, 15),
            'product_8' => $this->faker->optional()->numberBetween(1, 15),
            'product_9' => $this->faker->optional()->numberBetween(1, 15),
            'product_10' => $this->faker->optional()->numberBetween(1, 15),
            'product_11' => $this->faker->optional()->numberBetween(1, 15),
            'product_12' => $this->faker->optional()->numberBetween(1, 15),
            'product_13' => $this->faker->optional()->numberBetween(1, 15),
            'product_14' => $this->faker->optional()->numberBetween(1, 15),
            'product_15' => $this->faker->optional()->numberBetween(1, 15),
            'product_16' => $this->faker->optional()->numberBetween(1, 15),
            'product_17' => $this->faker->optional()->numberBetween(1, 15),
            'product_18' => $this->faker->optional()->numberBetween(1, 15),
            'product_19' => $this->faker->optional()->numberBetween(1, 15),
            'product_20' => $this->faker->optional()->numberBetween(1, 15),
            'product_21' => $this->faker->optional()->numberBetween(1, 15),
            'product_22' => $this->faker->optional()->numberBetween(1, 15),
            'product_23' => $this->faker->optional()->numberBetween(1, 15),
            'product_24' => $this->faker->optional()->numberBetween(1, 15),
            'product_25' => $this->faker->optional()->numberBetween(1, 15),
            'product_26' => $this->faker->optional()->numberBetween(1, 15),
            'product_27' => $this->faker->optional()->numberBetween(1, 15),
            'product_28' => $this->faker->optional()->numberBetween(1, 15),
            'product_29' => $this->faker->optional()->numberBetween(1, 15),
        ];
    }
}
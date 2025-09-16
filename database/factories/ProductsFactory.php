<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Products>
 */
class ProductsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'price' => $this->faker->randomFloat(2, 1000, 100000), // Price between 1k and 100k
            'stock' => $this->faker->numberBetween(0, 1000),
            'sku' => $this->faker->unique()->regexify('[A-Z]{3}[0-9]{6}'), // Format: ABC123456
            'is_active' => $this->faker->boolean(80), // 80% chance of being active
        ];
    }
}

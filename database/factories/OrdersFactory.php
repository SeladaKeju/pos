<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\Customers;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Orders>
 */
class OrdersFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customers::factory(),
            'order_number' => 'ORD-' . $this->faker->unique()->numerify('######'),
            'user_id' => User::factory(),
            'total_amount' => $this->faker->randomFloat(2, 10000, 500000), // Between 10k and 500k
        ];
    }
}

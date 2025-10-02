<?php

namespace Database\Seeders;

use App\Models\Orders;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrdersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure we have users before creating orders
        $userIds = User::pluck('id')->toArray();
        
        if (empty($userIds)) {
            $this->command->info('Creating users first...');
            User::factory()->count(5)->create();
            $userIds = User::pluck('id')->toArray();
        }

        // Create 200 orders using existing users
        for ($i = 0; $i < 200; $i++) {
            Orders::factory()->create([
                'user_id' => $userIds[array_rand($userIds)],
                'order_date' => fake()->dateTimeBetween('-30 days', 'now'),
                'status' => fake()->randomElement(['pending', 'completed', 'cancelled']),
                'is_paid' => fake()->boolean(80), // 80% chance of being paid
            ]);
        }
    }
}

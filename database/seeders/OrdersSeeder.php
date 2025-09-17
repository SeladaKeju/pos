<?php

namespace Database\Seeders;

use App\Models\Orders;
use App\Models\Customers;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrdersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure we have customers and users before creating orders
        $customerIds = Customers::pluck('id')->toArray();
        $userIds = User::pluck('id')->toArray();

        if (empty($customerIds)) {
            $this->command->info('Creating customers first...');
            Customers::factory()->count(20)->create();
            $customerIds = Customers::pluck('id')->toArray();
        }
        
        if (empty($userIds)) {
            $this->command->info('Creating users first...');
            User::factory()->count(5)->create();
            $userIds = User::pluck('id')->toArray();
        }

        // Create 200 orders using existing customers and users
        for ($i = 0; $i < 200; $i++) {
            Orders::factory()->create([
                'customer_id' => $customerIds[array_rand($customerIds)],
                'user_id' => $userIds[array_rand($userIds)]
            ]);
        }
    }
}

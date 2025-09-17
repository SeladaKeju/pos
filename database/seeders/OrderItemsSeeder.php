<?php

namespace Database\Seeders;

use App\Models\OrderItems;
use App\Models\Orders;
use App\Models\Products;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderItemsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure we have orders and products before creating order items
        $orderIds = Orders::pluck('id')->toArray();
        $productIds = Products::pluck('id')->toArray();

        if (empty($orderIds)) {
            $this->command->error('No orders found! Please run OrdersSeeder first.');
            return;
        }

        if (empty($productIds)) {
            $this->command->error('No products found! Please run ProductsSeeder first.');
            return;
        }

        $this->command->info('Creating order items...');

        // For each order, create 1-5 random order items
        foreach ($orderIds as $orderId) {
            $itemCount = fake()->numberBetween(1, 5);
            $usedProducts = [];

            for ($i = 0; $i < $itemCount; $i++) {
                // Avoid duplicate products in the same order
                $availableProducts = array_diff($productIds, $usedProducts);
                
                if (empty($availableProducts)) {
                    break; // No more unique products available
                }

                $productId = fake()->randomElement($availableProducts);
                $usedProducts[] = $productId;

                // Get the product to use its price as base
                $product = Products::find($productId);
                $basePrice = $product ? $product->price : fake()->randomFloat(2, 1000, 50000);

                OrderItems::create([
                    'order_id' => $orderId,
                    'product_id' => $productId,
                    'quantity' => fake()->numberBetween(1, 10),
                    'price' => $basePrice, // Use product's actual price
                ]);
            }
        }

        $this->command->info('Order items created successfully!');
    }
}

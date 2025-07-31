<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sale;
use App\Models\Customer;
use App\Models\Product;
use Faker\Factory as Faker;

class SaleSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        $customerIds = Customer::pluck('id')->toArray();
        $productIds = Product::pluck('id')->toArray();
        
        for ($i = 0; $i < 400; $i++) {
            Sale::create([
                'customer_id' => $faker->randomElement($customerIds),
                'product_id' => $faker->randomElement($productIds),
                'quantity' => $faker->numberBetween(1, 10),
                'created_at' => $faker->dateTimeBetween('-1 year', 'now'),
                'updated_at' => $faker->dateTimeBetween('-1 year', 'now'),
            ]);
        }
    }
} 
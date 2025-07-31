<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use Faker\Factory as Faker;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        $categories = ['Electronics', 'Clothing', 'Books', 'Home & Garden', 'Sports', 'Beauty', 'Toys', 'Automotive'];
        
        for ($i = 0; $i < 20; $i++) {
            Product::create([
                'name' => $faker->words(2, true),
                'category' => $faker->randomElement($categories),
                'price' => $faker->randomFloat(2, 10, 1000),
                'quantity' => $faker->numberBetween(10, 500),
            ]);
        }
    }
} 
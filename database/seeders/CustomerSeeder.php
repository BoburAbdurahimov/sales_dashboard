<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;
use Faker\Factory as Faker;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        $regions = ['North', 'South', 'East', 'West', 'Central'];
        $genders = ['male', 'female'];
        
        for ($i = 0; $i < 100; $i++) {
            Customer::create([
                'name' => $faker->name,
                'email' => $faker->unique()->safeEmail,
                'phone_number' => $faker->phoneNumber,
                'age' => $faker->numberBetween(18, 80),
                'gender' => $faker->randomElement($genders),
                'region' => $faker->randomElement($regions),
                'address' => $faker->address,
            ]);
        }
    }
} 
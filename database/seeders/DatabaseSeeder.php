<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            BrandSeeder::class,
            CategorySeeder::class,
            OrderStatusSeeder::class,
            PaymentTypeSeeder::class,
            PostSeeder::class,
            PromotionSeeder::class,
            UserSeeder::class,
        ]);
    }
}

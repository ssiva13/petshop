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
            UserSeeder::class,
            OrderStatusSeeder::class,
            CategorySeeder::class,
            BrandSeeder::class,
            PaymentTypeSeeder::class,
            PromotionSeeder::class,
            PostSeeder::class,
        ]);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        GenerateSampleAssets::generate();

        $this->call([
            UserSeeder::class,
            RestaurantSettingSeeder::class,
            CategoryAndProductSeeder::class,
            HomepageBannerSeeder::class,
            CouponSeeder::class,
        ]);
    }
}

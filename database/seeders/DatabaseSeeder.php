<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            BrandSeeder::class,
            FaqSeeder::class,
            PostSeeder::class,
            FeedbackSeeder::class,
            BannerSeeder::class,
            CategorySeeder::class,
            // CharacteristicSeeder::class,
            // AttributeSeeder::class,
        ]);
    }
}

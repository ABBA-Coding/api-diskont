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
            FaqSeeder::class,
            PostSeeder::class,
            FeedbackSeeder::class,
            BannerSeeder::class,
            // BrandSeeder::class, // from ProductSeeder
            // CategorySeeder::class, // from ProductSeeder
            ProductSeeder::class,
            CommentSeeder::class,
            // CharacteristicSeeder::class,
            // AttributeSeeder::class,
        ]);
    }
}

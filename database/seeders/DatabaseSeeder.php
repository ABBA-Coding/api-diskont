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
            RegionSeeder::class,
//            UserSeeder::class,
            ShowcaseSeeder::class,
            ExchangeRateSeeder::class,
            AttributeSeeder::class,
            DicoinSeeder::class,
            InfoSeeder::class,
            BarabanItemSeeder::class,
        ]);
    }
}

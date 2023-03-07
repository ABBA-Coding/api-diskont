<?php

namespace Database\Seeders;

use App\Models\Products\ProductInfo;
use App\Models\Products\ProductImage;
use App\Models\Products\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for($i=1; $i<31; $i++) {
            ProductInfo::factory()
                ->has(
                    Product::factory()
                        ->has(
                            ProductImage::factory()
                                ->count(4),
                            'images'
                        )
                        ->count(5)
                )
                ->count(1)
                ->create([
                    'default_product_id' => rand($i*5-4,$i*5),
                ]);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Products\ProductInfo;
use App\Models\Products\ProductImage;
use App\Models\Products\Product;
use DB;
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

        // add attribute options to products
        for($i=1; $i<31; $i++) {
            for($j=1; $j<rand(3,5); $j++) {
                DB::table('attribute_option_product')->insert([
                    'attribute_option_id' => rand(1,2400),
                    'product_id' => $i
                ]);
            }
        }

        // add characteristic options to products
        for($i=1; $i<31; $i++) {
            for($j=1; $j<rand(10,15); $j++) {
                DB::table('characteristic_option_product')->insert([
                    'characteristic_option_id' => rand(1,3600),
                    'product_id' => $i
                ]);
            }
        }
    }
}

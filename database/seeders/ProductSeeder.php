<?php

namespace Database\Seeders;

use App\Models\Attributes\Attribute;
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
        $this->call([
            BrandSeeder::class, // from ProductSeeder
            CategorySeeder::class, // from ProductSeeder
        ]);

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
            $product_attributes = Product::find($i)->info->category->attributes;
            foreach($product_attributes as $product_attribute) {
                $options_ids = Attribute::find($product_attribute->id)->options->pluck('id')->toArray();
                DB::table('attribute_option_product')->insert([
                    'attribute_option_id' => $options_ids[array_rand($options_ids)],
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

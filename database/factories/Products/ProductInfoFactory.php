<?php

namespace Database\Factories\Products;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductInfoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $name = [
            'ru' => 'ru ' . $this->faker->sentence(4),
            'uz' => 'uz ' . $this->faker->sentence(4),
            'en' => 'en ' . $this->faker->sentence(4),
        ];

        $desc = [
            'ru' => 'ru ' . $this->faker->sentence(12),
            'uz' => 'uz ' . $this->faker->sentence(12),
            'en' => 'en ' . $this->faker->sentence(12),
        ];


        return [
            'name' => $name,
            'for_search' => $name['ru'],
            'desc' => $desc,
            'brand_id' => rand(1,50),
            'category_id' => rand(21, 30),
            'default_product_id' => null,
        ];
    }
}

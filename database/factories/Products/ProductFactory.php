<?php

namespace Database\Factories\Products;

use Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $status = ['active', 'inactive'];

        return [
            'model' => $this->faker->sentence(1),
            'price' => $this->faker->numberBetween(50000, 15000000),
            'is_popular' => rand(0,1),
            'product_of_the_day' => rand(0,1),
            'status' => $status[rand(0,1)],
            'slug' => Str::slug($this->faker->sentence(3), '-'),
        ];
    }
}

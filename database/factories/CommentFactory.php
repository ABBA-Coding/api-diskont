<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => 1,
            'product_info_id' => rand(1,30),
            'comment' => $this->faker->sentence(10),
            'stars' => rand(2,5),
        ];
    }
}

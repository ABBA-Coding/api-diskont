<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BannerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $type = ['main', 'promo', 'small'];
        return [
            'img' => [
                'ru' => $this->faker->imageUrl(),
                'uz' => $this->faker->imageUrl(),
                'en' => $this->faker->imageUrl(),
            ],
            'link' => [
                'ru' => 'https://test.loftcity.uz/',
                'uz' => 'https://test.loftcity.uz/',
                'en' => 'https://test.loftcity.uz/',
            ],
            'type' => $type[array_rand($type)],
        ];
    }
}

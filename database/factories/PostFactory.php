<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Str;

class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $title = $this->faker->sentence(10);
        $desc = $this->faker->realText(200);
        return [
            'title' => [
                'ru' => 'ru ' . $title,
                'uz' => 'uz ' . $title,
                'en' => 'en ' . $title,
            ],
            'desc' => [
                'ru' => 'ru ' . $desc,
                'uz' => 'uz ' . $desc,
                'en' => 'en ' . $desc,
            ],
            'img' => $this->faker->imageUrl(),
            'for_search' => $title,
            'slug' => Str::slug($title, '-'),
        ];
    }
}

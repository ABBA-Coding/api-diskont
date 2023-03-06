<?php

namespace Database\Factories\Faqs;

use Illuminate\Database\Eloquent\Factories\Factory;

class FaqCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $title = [
            'ru' => 'ru ' . $this->faker->sentence(5),
            'uz' => 'uz ' . $this->faker->sentence(5),
            'en' => 'en ' . $this->faker->sentence(5),
        ];

        return [
            'title' => $title,
            'for_search' => $title['ru'],
        ];
    }
}

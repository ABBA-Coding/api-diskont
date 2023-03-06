<?php

namespace Database\Factories\Attributes;

use Illuminate\Database\Eloquent\Factories\Factory;

class AttributeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $name = $this->faker->sentence(2);
        return [
            'name' => [
                'ru' => 'ru ' . $name,
                'uz' => 'uz ' . $name,
                'en' => 'en ' . $name,
            ],
            'keywords' => $this->faker->sentence(10),
            'for_search' => 'ru ' . $name,
        ];
    }
}

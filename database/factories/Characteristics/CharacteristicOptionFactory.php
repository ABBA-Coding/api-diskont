<?php

namespace Database\Factories\Characteristics;

use Illuminate\Database\Eloquent\Factories\Factory;

class CharacteristicOptionFactory extends Factory
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
            'for_search' => 'ru ' . $name,
        ];
    }
}

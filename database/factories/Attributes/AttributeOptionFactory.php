<?php

namespace Database\Factories\Attributes;

use Illuminate\Database\Eloquent\Factories\Factory;

class AttributeOptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $name = $this->faker->sentence(1);
        return [
            'name' => [
                'ru' => 'ru_' . $name,
                'uz' => 'uz_' . $name,
                'en' => 'en_' . $name,
            ],
            'for_search' => 'ru_' . $name,
        ];
    }
}

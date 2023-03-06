<?php

namespace Database\Factories\Faqs;

use Illuminate\Database\Eloquent\Factories\Factory;

class FaqFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $question = [
            'ru' => 'ru ' . $this->faker->sentence(10),
            'uz' => 'uz ' . $this->faker->sentence(10),
            'en' => 'en ' . $this->faker->sentence(10),
        ];
        $answer = [
            'ru' => 'ru ' . $this->faker->sentence(10),
            'uz' => 'uz ' . $this->faker->sentence(10),
            'en' => 'en ' . $this->faker->sentence(10),
        ];
        
        return [
            'question' => $question,
            'answer' => $answer,
            'for_search' => $question['ru'] . ' ' . $answer['ru']
        ];
    }
}

<?php

namespace Database\Factories\Feedbacks;

use Illuminate\Database\Eloquent\Factories\Factory;

class FeedbackFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'feedback' => $this->faker->realText(200),
            'company' => $this->faker->sentence(2),
            'logo' => $this->faker->imageUrl(),
        ];
    }
}

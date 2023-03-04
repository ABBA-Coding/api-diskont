<?php

namespace Database\Factories;

use App\Models\Feedbacks\Feedback;
use Illuminate\Database\Eloquent\Factories\Factory;

class FeedbackFactory extends Factory
{

    protected $model = Feedback::class;
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
            // 'images' => [
            //     $this->faker->imageUrl(),
            //     $this->faker->imageUrl(),
            //     $this->faker->imageUrl(),
            // ]
        ];
    }
}

<?php

namespace Database\Factories\Feedbacks;

use Str;
use Illuminate\Support\Facades\Storage;
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
        $feedback = [
            'ru' => 'ru ' . $this->faker->realText(200),
            'uz' => 'uz ' . $this->faker->realText(200),
            'en' => 'en ' . $this->faker->realText(200),
        ];

        $rand_int = rand(1,13);
        $rand_string = Str::random(16);
        Storage::disk('public')->copy('delete/' . $rand_int . '.png', 'uploads/feedbacks/' . $rand_string . '.png');
        Storage::disk('public')->copy('delete/' . $rand_int . '.png', 'uploads/feedbacks/200/' . $rand_string . '.png');
        Storage::disk('public')->copy('delete/' . $rand_int . '.png', 'uploads/feedbacks/600/' . $rand_string . '.png');


        return [
            'feedback' => $feedback,
            'company' => $this->faker->sentence(2),
            'logo' => $rand_string . '.png',
        ];
    }
}

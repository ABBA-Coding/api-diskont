<?php

namespace Database\Factories\Feedbacks;

use Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\Factory;

class FeedbackImageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $rand_int = rand(1,13);
        $rand_string = Str::random(16);
        Storage::disk('public')->copy('delete/' . $rand_int . '.png', 'uploads/feedbacks/' . $rand_string . '.png');
        Storage::disk('public')->copy('delete/' . $rand_int . '.png', 'uploads/feedbacks/200/' . $rand_string . '.png');
        Storage::disk('public')->copy('delete/' . $rand_int . '.png', 'uploads/feedbacks/600/' . $rand_string . '.png');

        return [
            'img' => $rand_string . '.png'
        ];
    }
}

<?php

namespace Database\Factories;

use Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $title = [
            'ru' => 'ru ' . $this->faker->sentence(10),
            'uz' => 'uz ' . $this->faker->sentence(10),
            'en' => 'en ' . $this->faker->sentence(10),
        ];
        $desc = [
            'ru' => 'ru ' . $this->faker->sentence(30),
            'uz' => 'uz ' . $this->faker->sentence(30),
            'en' => 'en ' . $this->faker->sentence(30),
        ];

        $rand_int = rand(1,13);
        $rand_string = Str::random(16);
        Storage::disk('public')->copy('delete/' . $rand_int . '.png', 'uploads/posts/' . $rand_string . '.png');
        Storage::disk('public')->copy('delete/' . $rand_int . '.png', 'uploads/posts/200/' . $rand_string . '.png');
        Storage::disk('public')->copy('delete/' . $rand_int . '.png', 'uploads/posts/600/' . $rand_string . '.png');

        
        return [
            'title' => $title,
            'desc' => $desc,
            'img' => $rand_string . '.png',
            'for_search' => $title['ru'],
            'slug' => Str::slug($title['ru'], '-'),
        ];
    }
}

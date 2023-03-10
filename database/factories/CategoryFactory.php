<?php

namespace Database\Factories;

use Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $name = [
            'ru' => 'ru ' . $this->faker->sentence(2),
            'uz' => 'uz ' . $this->faker->sentence(2),
            'en' => 'en ' . $this->faker->sentence(2),
        ];
        $desc = [
            'ru' => 'ru ' . $this->faker->sentence(15),
            'uz' => 'uz ' . $this->faker->sentence(15),
            'en' => 'en ' . $this->faker->sentence(15),
        ];

        $rand_int = rand(1,13);
        $rand_string_for_img = Str::random(16);
        Storage::disk('public')->copy('delete/' . $rand_int . '.png', 'uploads/categories/images/' . $rand_string_for_img . '.png');
        Storage::disk('public')->copy('delete/' . $rand_int . '.png', 'uploads/categories/images/200/' . $rand_string_for_img . '.png');
        Storage::disk('public')->copy('delete/' . $rand_int . '.png', 'uploads/categories/images/600/' . $rand_string_for_img . '.png');

        $rand_int = rand(1,13);
        $rand_string_for_icon = Str::random(16);
        Storage::disk('public')->copy('delete/' . $rand_int . '.png', 'uploads/categories/icons/' . $rand_string_for_icon . '.png');
        Storage::disk('public')->copy('delete/' . $rand_int . '.png', 'uploads/categories/icons/200/' . $rand_string_for_icon . '.png');
        Storage::disk('public')->copy('delete/' . $rand_int . '.png', 'uploads/categories/icons/600/' . $rand_string_for_icon . '.png');


        return [
            'name' => $name,
            'parent_id' => null,
            'is_popular' => rand(0,1),
            'desc' => $desc,
            'icon' => $rand_string_for_icon . '.png',
            'img' => $rand_string_for_img . '.png',
            'position' => rand(1,80),
            'slug' => Str::slug($name['ru'], '-'),
            'for_search' => $name['ru'],
        ];
    }
}

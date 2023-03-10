<?php

namespace Database\Factories;

use Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\Factory;

class BrandFactory extends Factory
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
        Storage::disk('public')->copy('delete/' . $rand_int . '.png', 'uploads/brands/' . $rand_string . '.png');
        Storage::disk('public')->copy('delete/' . $rand_int . '.png', 'uploads/brands/200/' . $rand_string . '.png');
        Storage::disk('public')->copy('delete/' . $rand_int . '.png', 'uploads/brands/600/' . $rand_string . '.png');

        $name = $this->faker->sentence(2);
        

        return [
            'name' => $name,
            'logo' => $rand_string . '.png',
            'slug' => Str::slug($name, '-'),
        ];
    }
}

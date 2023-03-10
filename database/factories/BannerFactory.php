<?php

namespace Database\Factories;

use Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\Factory;

class BannerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $images = [];
        for($i=0; $i<3; $i++) {
            $rand_int = rand(1,13);
            $rand_string = Str::random(16);
            Storage::disk('public')->copy('delete/' . $rand_int . '.png', 'uploads/banners/' . $rand_string . '.png');
            Storage::disk('public')->copy('delete/' . $rand_int . '.png', 'uploads/banners/200/' . $rand_string . '.png');
            Storage::disk('public')->copy('delete/' . $rand_int . '.png', 'uploads/banners/600/' . $rand_string . '.png');
            $images[] = $rand_string . '.png';
        }

        $type = ['main', 'promo', 'small'];


        return [
            'img' => [
                'ru' => $images[0],
                'uz' => $images[1],
                'en' => $images[2],
            ],
            'link' => [
                'ru' => 'https://test.loftcity.uz/',
                'uz' => 'https://test.loftcity.uz/',
                'en' => 'https://test.loftcity.uz/',
            ],
            'type' => $type[array_rand($type)],
        ];
    }
}

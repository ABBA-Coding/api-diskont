<?php

namespace Database\Factories\Products;

use Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductImageFactory extends Factory
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
        Storage::disk('public')->copy('delete/' . $rand_int . '.png', 'uploads/products/' . $rand_string . '.png');
        Storage::disk('public')->copy('delete/' . $rand_int . '.png', 'uploads/products/200/' . $rand_string . '.png');
        Storage::disk('public')->copy('delete/' . $rand_int . '.png', 'uploads/products/600/' . $rand_string . '.png');

        return [
            'img' => $rand_string . '.png'
        ];
    }
}

<?php

namespace Database\Seeders;

use App\Models\Showcase;
use Str;
use Illuminate\Database\Seeder;

class ShowcaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                'name' => [
                    'ru' => 'Бестселлеры',
                    'en' => 'Bestsellers',
                    'uz' => 'Ko\'p sotilganlar'
                ],
                'for_search' => 'Бестселлеры',
                'slug' => Str::slug('Бестселлеры')
            ],
            [
                'name' => [
                    'ru' => 'Топ товары',
                    'en' => 'Top products',
                    'uz' => 'Top tovarlar'
                ],
                'for_search' => 'Топ товары',
                'slug' => Str::slug('Топ товары')
            ],
            [
                'name' => [
                    'ru' => 'Наборы для геймеров',
                    'en' => 'Sets for gamers',
                    'uz' => 'Gamerlar uchun komplektlar'
                ],
                'for_search' => 'Наборы для геймеров',
                'slug' => Str::slug('Наборы для геймеров')
            ],
            [
                'name' => [
                    'ru' => 'Самые дешевые',
                    'en' => 'The cheapest',
                    'uz' => 'Eng arzon'
                ],
                'for_search' => 'Самые дешевые',
                'slug' => Str::slug('Самые дешевые')
            ],
            [
                'name' => [
                    'ru' => 'Новые продукты',
                    'en' => 'New products',
                    'uz' => 'Yangi tovarlar'
                ],
                'for_search' => 'Новые продукты',
                'slug' => Str::slug('Новые продукты')
            ],
            [
                'name' => [
                    'ru' => 'Наборы для геймеров 2',
                    'en' => 'Sets for gamers 2',
                    'uz' => 'Gamerlar uchun komplektlar 2'
                ],
                'for_search' => 'Наборы для геймеров 2',
                'slug' => Str::slug('Наборы для геймеров 2')
            ],
        ];
        foreach($data as $item) {
            Showcase::create($item);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Attributes\Attribute;
use App\Models\Attributes\AttributeOption;
use Illuminate\Database\Seeder;

class AttributeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $attributes = [
            [
                'id' => 1,
                'name' => [
                    'ru' => 'Цвет',
                    'uz' => 'Rangi',
                    'en' => 'Color'
                ],
                'keywords' => 'Цвет',
                'for_search' => 'Цвет',
            ]
        ];

        $options = [
            [
                'id' => 1,
                'attribute_id' => 1,
                'name' => [
                    'ru' => '#000000'
                ],
                'for_search' => '#000000',
                'position' => 1,
            ],
            [
                'id' => 2,
                'attribute_id' => 1,
                'name' => [
                    'ru' => '#ffffff'
                ],
                'for_search' => '#ffffff',
                'position' => 2,
            ],
        ];

        foreach ($attributes as $key => $value) {
            Attribute::create($value);    
        }
        foreach ($options as $key => $value) {
            AttributeOption::create($value);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Attributes\Attribute;
use App\Models\Attributes\AttributeOption;
use App\Models\Characteristics\Characteristic;
use App\Models\Characteristics\CharacteristicGroup;
use App\Models\Characteristics\CharacteristicOption;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Category::factory()
            ->has(Attribute::factory()
                ->has(AttributeOption::factory()->count(5), 'options')
                ->count(4))
            ->has(CharacteristicGroup::factory()
                ->has(Characteristic::factory()
                    ->has(CharacteristicOption::factory()->count(5), 'options')
                    ->count(6))
                ->count(5), 'characteristic_groups')
            ->count(30)
            ->create([
                'parent_id' => null
            ]);

        Category::factory()
            ->has(Attribute::factory()
                ->has(AttributeOption::factory()->count(5), 'options')
                ->count(4))
            ->has(CharacteristicGroup::factory()
                ->has(Characteristic::factory()
                    ->has(CharacteristicOption::factory()->count(5), 'options')
                    ->count(6))
                ->count(5), 'characteristic_groups')
            ->count(40)
            ->create([
                'parent_id' => rand(1,30)
            ]);

        Category::factory()
            ->has(Attribute::factory()
                ->has(AttributeOption::factory()->count(5), 'options')
                ->count(4))
            ->has(CharacteristicGroup::factory()
                ->has(Characteristic::factory()
                    ->has(CharacteristicOption::factory()->count(5), 'options')
                    ->count(6))
                ->count(5), 'characteristic_groups')
            ->count(50)
            ->create([
                'parent_id' => rand(31,70)
            ]);
    }
}

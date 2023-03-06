<?php

namespace Database\Seeders;

use App\Models\Characteristics\Characteristic;
use App\Models\Characteristics\CharacteristicGroup;
use App\Models\Characteristics\CharacteristicOption;
use Illuminate\Database\Seeder;

class CharacteristicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        CharacteristicGroup::factory()
            ->has(Characteristic::factory()
                ->has(CharacteristicOption::factory()->count(8), 'options')
                ->count(12))
            ->count(5)
            ->create();
    }
}

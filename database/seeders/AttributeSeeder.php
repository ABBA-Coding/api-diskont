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
        Attribute::factory()
            ->has(AttributeOption::factory()->count(5), 'options')
            ->count(10)
            ->create();
    }
}

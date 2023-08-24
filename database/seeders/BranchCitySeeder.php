<?php

namespace Database\Seeders;

use App\Models\BranchCity;
use Illuminate\Database\Seeder;

class BranchCitySeeder extends Seeder
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
        		'id' => 1,
        		'region_id' => 1,
        		'name' => 'Республика Каракалпакстан',
        	],
        	[
        		'id' => 2,
        		'region_id' => 2,
        		'name' => 'Андижанская область',
        	],
        	[
        		'id' => 3,
        		'region_id' => 3,
        		'name' => 'Бухарская область',
        	],
        	[
        		'id' => 4,
        		'region_id' => 4,
        		'name' => 'Джизакская область',
        	],
        	[
        		'id' => 5,
        		'region_id' => 5,
        		'name' => 'Кашкадарьинская область',
        	],
        	[
        		'id' => 6,
        		'region_id' => 6,
        		'name' => 'Навоийская область',
        	],
        	[
        		'id' => 7,
        		'region_id' => 7,
        		'name' => 'Наманганская область',
        	],
        	[
        		'id' => 8,
        		'region_id' => 8,
        		'name' => 'Самаркандская область',
        	],
        	[
        		'id' => 9,
        		'region_id' => 9,
        		'name' => 'Сурхандарьинская область',
        	],
        	[
        		'id' => 10,
        		'region_id' => 10,
        		'name' => 'Сырдарьинская область',
        	],
        	[
        		'id' => 11,
        		'region_id' => 11,
        		'name' => 'Ташкентская область',
        	],
        	[
        		'id' => 12,
        		'region_id' => 12,
        		'name' => 'Ферганская область',
        	],
        	[
        		'id' => 13,
        		'region_id' => 13,
        		'name' => 'Хорезмская область',
        	],
        	[
        		'id' => 14,
        		'region_id' => 14,
        		'name' => 'Ташкент',
        	],
        ];

        foreach ($data as $key => $value) {
        	BranchCity::create($value);
        }
    }
}

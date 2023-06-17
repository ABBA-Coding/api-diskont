<?php

namespace Database\Seeders;

use App\Models\Settings\Region;
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     * @throws \Exception
     */
    public function run()
    {
        $regions = simplexml_load_file(app_path('Http/files/settings/regions.xml'));

        foreach ($regions->table_regions->regions as $region) {
            $region = (array)$region;

            Region::create([
                'id' => $region['@attributes']['id'],
                'name' => [
                    'uz' => $region['@attributes']['name_uz'],
                    'ru' => $region['@attributes']['name_ru']
                ],
                'for_search' => $region['@attributes']['name_ru']
            ]);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Settings\District;
use App\Models\Settings\Region;
use App\Models\Settings\Village;
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
        $districts = simplexml_load_file(app_path('Http/files/settings/districts.xml'));
        $villages = simplexml_load_file(app_path('Http/files/settings/villages.xml'));

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

        foreach ($districts->table_districts->districts as $district) {
            $district = (array)$district;

            District::create([
                'id' => $district['@attributes']['id'],
                'region_id' => $district['@attributes']['region_id'],
                'name' => [
                    'uz' => $district['@attributes']['name_uz'],
                    'ru' => $district['@attributes']['name_ru']
                ],
                'for_search' => $district['@attributes']['name_ru']
            ]);
        }

        foreach ($villages->table_villages->villages as $village) {
            $village = (array)$village;

            Village::create([
                'id' => $village['@attributes']['id'],
                'district_id' => $village['@attributes']['district_id'],
                'name' => [
                    'uz' => $village['@attributes']['name_uz'],
                    'ru' => $village['@attributes']['name_ru']
                ],
                'for_search' => $village['@attributes']['name_ru']
            ]);
        }
    }
}

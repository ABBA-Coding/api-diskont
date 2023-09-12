<?php

namespace Database\Seeders;

use App\Models\BarabanItem;
use Illuminate\Database\Seeder;

class BarabanItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for($i=0; $i<12; $i++) {
            $item = [
                'id' => $i+1,
                'position' => $i+1,
                'count' => $i+1
            ];

            if(!BarabanItem::find($item['id'])) BarabanItem::create($item);
        }
    }
}

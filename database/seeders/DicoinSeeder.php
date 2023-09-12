<?php

namespace Database\Seeders;

use App\Models\Dicoin\Dicoin;
use Illuminate\Database\Seeder;

class DicoinSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
        	'sum_to_dicoin' => 100000,
        	'dicoin_to_sum' => 10000,
        	'dicoin_to_reg' => 15
        ];

        Dicoin::create($data);
    }
}

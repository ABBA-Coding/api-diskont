<?php

namespace Database\Seeders;

use App\Models\Faqs\Faq;
use App\Models\Faqs\FaqCategory;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        FaqCategory::factory()
            ->has(Faq::factory()->count(5))
            ->count(10)
            ->create();
    }
}

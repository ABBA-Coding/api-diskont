<?php

namespace Database\Seeders;

use App\Models\Feedbacks\Feedback;
use Illuminate\Database\Seeder;

class FeedbackSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Feedback::factory()
            ->count(15)
            ->create();
    }
}

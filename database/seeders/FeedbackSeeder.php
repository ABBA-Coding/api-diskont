<?php

namespace Database\Seeders;

use App\Models\Feedbacks\Feedback;
use App\Models\Feedbacks\FeedbackImage;
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
            ->has(FeedbackImage::factory()->count(3), 'images')
            ->count(2)
            ->create();
    }
}

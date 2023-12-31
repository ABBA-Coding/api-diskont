<?php

namespace App\Models\Feedbacks;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;

    protected $fillable = [
        'feedback',
        'company',
        'logo',
    ];

    protected $casts = [
        'feedback' => 'array',
    ];

    public function images()
    {
        return $this->hasMany(FeedbackImage::class, 'feedback_id')->select('id', 'feedback_id', 'img');
    }

    public function translatable(): array
    {
        return [
            'feedback',
        ];
    }
}

<?php

namespace App\Models\Feedbacks;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeedbackImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'feedback_id',
        'img',
    ];

    public function feedback()
    {
        return $this->belongsTo(Feedback::class, 'feedback_id');
    }
}

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




    protected $appends = [
        'sm_img',
        'md_img',
        'lg_img',
    ];

    public function getLgImgAttribute()
    {
        return $this->img ? (url('/uploads/feedbacks') . '/' . $this->img) : null;
    }
    public function getSmImgAttribute()
    {
        return $this->img ? (url('/uploads/feedbacks/200') . '/' . $this->img) : null;
    }
    public function getMdImgAttribute()
    {
        return $this->img ? (url('/uploads/feedbacks/600') . '/' . $this->img) : null;
    }
}

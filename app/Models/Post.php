<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'desc',
        'img',
        'for_search',
    ];

    protected $casts = [
        'title' => 'array',
        'desc' => 'array',
    ];




    protected $appends = [
        'sm_img',
        'md_img',
        'lg_img',
    ];

    public function getLgImgAttribute()
    {
        return $this->img ? (url('/uploads/posts') . '/' . $this->img) : null;
    }
    public function getSmImgAttribute()
    {
        return $this->img ? (url('/uploads/posts/200') . '/' . $this->img) : null;
    }
    public function getMdImgAttribute()
    {
        return $this->img ? (url('/uploads/posts/600') . '/' . $this->img) : null;
    }
}

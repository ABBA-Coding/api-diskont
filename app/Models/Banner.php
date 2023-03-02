<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'img',
        'link',
        'type',
    ];

    protected $casts = [
        'img' => 'array',
        'link' => 'array'
    ];

    protected $appends = [
        'sm_img',
        'md_img',
        'lg_img',
    ];

    public function getLgImgAttribute()
    {
        $img = $this->img;

        foreach($img as $key => $val) {
            $img[$key] = url('/uploads/banners') . '/' . $img[$key];
        }

        return $img;
    }
    public function getSmImgAttribute()
    {
        $img = $this->img;

        foreach($img as $key => $val) {
            $img[$key] = url('/uploads/banners/200') . '/' . $img[$key];
        }

        return $img;
    }
    public function getMdImgAttribute()
    {
        $img = $this->img;

        foreach($img as $key => $val) {
            $img[$key] = url('/uploads/banners/600') . '/' . $img[$key];
        }

        return $img;
    }
}

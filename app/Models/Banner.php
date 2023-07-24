<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'showcase_id',
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
        return $this->img ? url('/uploads/banners/200') . '/' . $this->img : null;
        // $img = $this->img;

        // foreach($img as $key => $val) {
        //     $img[$key] = url('/uploads/banners') . '/' . $img[$key];
        // }

        // return $img;
    }
    public function getSmImgAttribute()
    {
        return $this->img ? url('/uploads/banners/200') . '/' . $this->img : null;
        // $img = $this->img;

        // foreach($img as $key => $val) {
        //     $img[$key] = url('/uploads/banners/200') . '/' . $img[$key];
        // }

        // return $img;
    }
    public function getMdImgAttribute()
    {
        return $this->img ? url('/uploads/banners/200') . '/' . $this->img : null;
        // $img = $this->img;

        // foreach($img as $key => $val) {
        //     $img[$key] = url('/uploads/banners/600') . '/' . $img[$key];
        // }

        // return $img;
    }

    public function showcase(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Showcase::class);
    }

    public function translatable(): array
    {
        return [
            'img',
            'link',
        ];
    }
}

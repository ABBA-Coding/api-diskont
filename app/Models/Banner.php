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
        'm_img',
        'link',
        'type',
    ];

    protected $casts = [
        'img' => 'array',
        'm_img' => 'array',
        'link' => 'array'
    ];

    protected $appends = [
        'sm_img',
        'md_img',
        'lg_img',
        'sm_m_img',
        'md_m_img',
        'lg_m_img',
    ];

    public function getLgImgAttribute()
    {
        if(!$this->img) return null;
        if(is_string($this->img)) return $this->img ? url('/uploads/banners') . '/' . $this->img : null;

        $img = $this->img;

        foreach($img as $key => $val) {
            $img[$key] = url('/uploads/banners') . '/' . $img[$key];
        }

        return $img;
    }
    public function getSmImgAttribute()
    {
        if(!$this->img) return null;
        if(is_string($this->img)) return $this->img ? url('/uploads/banners/200') . '/' . $this->img : null;

        $img = $this->img;

        foreach($img as $key => $val) {
            $img[$key] = url('/uploads/banners/200') . '/' . $img[$key];
        }

        return $img;
    }
    public function getMdImgAttribute()
    {
        if(!$this->img) return null;
        if(is_string($this->img)) return $this->img ? url('/uploads/banners/600') . '/' . $this->img : null;

        $img = $this->img;

        foreach($img as $key => $val) {
            $img[$key] = url('/uploads/banners/600') . '/' . $img[$key];
        }

        return $img;
    }

    public function getLgMImgAttribute()
    {
        if(!$this->m_img) return null;
        if(is_string($this->m_img)) return $this->m_img ? url('/uploads/banners') . '/' . $this->m_img : null;

        $img = $this->m_img;

        foreach($img as $key => $val) {
            $img[$key] = url('/uploads/banners') . '/' . $img[$key];
        }

        return $img;
    }
    public function getSmMImgAttribute()
    {
        if(!$this->m_img) return null;
        if(is_string($this->m_img)) return $this->m_img ? url('/uploads/banners/200') . '/' . $this->m_img : null;

        $img = $this->m_img;

        foreach($img as $key => $val) {
            $img[$key] = url('/uploads/banners/200') . '/' . $img[$key];
        }

        return $img;
    }
    public function getMdMImgAttribute()
    {
        if(!$this->m_img) return null;
        if(is_string($this->m_img)) return $this->m_img ? url('/uploads/banners/600') . '/' . $this->m_img : null;

        $img = $this->m_img;

        foreach($img as $key => $val) {
            $img[$key] = url('/uploads/banners/600') . '/' . $img[$key];
        }

        return $img;
    }

    public function showcase(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Showcase::class);
    }

    public function translatable(): array
    {
        return [
            'link',
        ];
    }
}

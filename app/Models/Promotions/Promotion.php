<?php

namespace App\Models\Promotions;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'short_name',
        'product_card_text',
        'desc',
        'short_name_icon',
        'short_name_icon_svg',
        'short_name_first_color',
        'short_name_last_color',
        'banner',
        'start_date',
        'end_date',
        'sticker',
        'sticker_svg',
        'product_card_text_color',
        'product_card_back_color',
        'for_search',
    ];

    protected $casts = [
        'short_name' => 'array',
        'name' => 'array',
        'desc' => 'array',
        'product_card_text' => 'array',
    ];




    protected $appends = [
        'sm_banner',
        'md_banner',
        'lg_banner',

        'sm_sticker',
        'md_sticker',
        'lg_sticker',

        'sm_short_name_icon',
        'md_short_name_icon',
        'lg_short_name_icon',
    ];

    public function getLgBannerAttribute()
    {
        return $this->img ? (url('/uploads/promotions/banners') . '/' . $this->img) : null;
    }
    public function getSmBannerAttribute()
    {
        return $this->img ? (url('/uploads/promotions/banners/200') . '/' . $this->img) : null;
    }
    public function getMdBannerAttribute()
    {
        return $this->img ? (url('/uploads/promotions/banners/600') . '/' . $this->img) : null;
    }

    public function getLgStickerAttribute()
    {
        return $this->img ? (url('/uploads/promotions/banners') . '/' . $this->img) : null;
    }
    public function getSmStickerAttribute()
    {
        return $this->img ? (url('/uploads/promotions/banners/200') . '/' . $this->img) : null;
    }
    public function getMdStickerAttribute()
    {
        return $this->img ? (url('/uploads/promotions/banners/600') . '/' . $this->img) : null;
    }

    public function getLgShortNameIconAttribute()
    {
        return $this->img ? (url('/uploads/promotions/banners') . '/' . $this->img) : null;
    }
    public function getSmShortNameIconAttribute()
    {
        return $this->img ? (url('/uploads/promotions/banners/200') . '/' . $this->img) : null;
    }
    public function getMdShortNameIconAttribute()
    {
        return $this->img ? (url('/uploads/promotions/banners/600') . '/' . $this->img) : null;
    }


    public function translatable(): array
    {
        return [
            'short_name',
            'name',
            'desc',
            'product_card_text'
        ];
    }
}

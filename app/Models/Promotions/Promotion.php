<?php

namespace App\Models\Promotions;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Products\Product;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'short_name',
        'short_name_text_color',
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
        'slug',
    ];

    protected $casts = [
        'short_name' => 'array',
        'name' => 'array',
        'desc' => 'array',
        'product_card_text' => 'array',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_promotion', 'promotion_id', 'product_id');
    }




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
        return $this->banner ? (url('/uploads/promotions/banners') . '/' . $this->banner) : null;
    }
    public function getSmBannerAttribute()
    {
        return $this->banner ? (url('/uploads/promotions/banners/200') . '/' . $this->banner) : null;
    }
    public function getMdBannerAttribute()
    {
        return $this->banner ? (url('/uploads/promotions/banners/600') . '/' . $this->banner) : null;
    }

    public function getLgStickerAttribute()
    {
        return $this->sticker ? (url('/uploads/promotions/stickers') . '/' . $this->sticker) : null;
    }
    public function getSmStickerAttribute()
    {
        return $this->sticker ? (url('/uploads/promotions/stickers/200') . '/' . $this->sticker) : null;
    }
    public function getMdStickerAttribute()
    {
        return $this->sticker ? (url('/uploads/promotions/stickers/600') . '/' . $this->sticker) : null;
    }

    public function getLgShortNameIconAttribute()
    {
        return $this->short_name_icon ? (url('/uploads/promotions/short_name_icon') . '/' . $this->short_name_icon) : null;
    }
    public function getSmShortNameIconAttribute()
    {
        return $this->short_name_icon ? (url('/uploads/promotions/short_name_icon/200') . '/' . $this->short_name_icon) : null;
    }
    public function getMdShortNameIconAttribute()
    {
        return $this->short_name_icon ? (url('/uploads/promotions/short_name_icon/600') . '/' . $this->short_name_icon) : null;
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

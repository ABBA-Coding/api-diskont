<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bar extends Model
{
    use HasFactory;

    protected $fillable = [
        'promotion_id',
        'category_id',
        'name',
        'icon',
        'icon_svg',
        'text_color',
        'color1',
        'color2',
        'for_search',
        'position',
    ];

    protected $casts = [
        'name' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class)->select('id', 'slug');
    }

    public function promotion()
    {
        return $this->belongsTo(Promotions\Promotion::class);
    }




    protected $appends = [
        'sm_icon',
        'md_icon',
        'lg_icon',
    ];

    public function getLgIconAttribute()
    {
        return $this->icon ? (url('/uploads/bars') . '/' . $this->icon) : null;
    }
    public function getSmIconAttribute()
    {
        return $this->icon ? (url('/uploads/bars/200') . '/' . $this->icon) : null;
    }
    public function getMdIconAttribute()
    {
        return $this->icon ? (url('/uploads/bars/600') . '/' . $this->icon) : null;
    }

    public function translatable(): array
    {
        return [
            'name',
        ];
    }
}

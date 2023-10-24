<?php

namespace App\Models;

use App\Models\Products\Product;
use App\Traits\CategoryTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory, CategoryTrait;

    protected $fillable = [
        'c_id',
        'name',
        'parent_id',
        'parent_c_id',
        'is_popular',
        'desc',
        'icon',
        'icon_svg',
        'img',
        'for_search',
        'position',
        'slug',
        'is_active',
        'meta_keywords',
        'meta_desc',
    ];

    protected $casts = [
        'name' => 'array',
        'desc' => 'array',
        'meta_keywords' => 'array',
        'meta_desc' => 'array',
    ];

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id')->with('parent')->select('id', 'name', 'parent_id', 'slug');
    }

    public function attributes()
    {
        return $this->belongsToMany(Attributes\Attribute::class);
    }

    public function characteristic_groups()
    {
        return $this->belongsToMany(Characteristics\CharacteristicGroup::class);
    }

    public function product_infos()
    {
        return $this->hasMany(Products\ProductInfo::class);
    }





    protected $appends = [
        'sm_img',
        'md_img',
        'lg_img',

        'lg_icon',
        'md_icon',
        'sm_icon',
    ];

    public function getLgImgAttribute()
    {
        return $this->img ? (url('/uploads/categories/images') . '/' . $this->img) : null;
    }
    public function getSmImgAttribute()
    {
        return $this->img ? (url('/uploads/categories/images/200') . '/' . $this->img) : null;
    }
    public function getMdImgAttribute()
    {
        return $this->img ? (url('/uploads/categories/images/600') . '/' . $this->img) : null;
    }

    public function getLgIconAttribute()
    {
        return $this->icon ? (url('/uploads/categories/icons') . '/' . $this->icon) : null;
    }
    public function getSmIconAttribute()
    {
        return $this->icon ? (url('/uploads/categories/icons/200') . '/' . $this->icon) : null;
    }
    public function getMdIconAttribute()
    {
        return $this->icon ? (url('/uploads/categories/icons/600') . '/' . $this->icon) : null;
    }

    public function translatable(): array
    {
        return [
            'name',
            'desc',
            'meta_keywords',
            'meta_desc',
        ];
    }
}

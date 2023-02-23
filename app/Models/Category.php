<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'parent_id',
        'is_popular',
        'desc',
        'icon',
        'img',
        'for_search',
    ];

    protected $casts = [
        'name' => 'array',
        'desc' => 'array',
    ];

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function attribute_groups()
    {
        return $this->belongsToMany(Attributes\AttributeGroup::class);
    }

    public function characteristic_groups()
    {
        return $this->belongsToMany(Characteristics\CharacteristicGroup::class);
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
}
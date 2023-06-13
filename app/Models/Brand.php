<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'logo',
        'slug',
        'is_top',
    ];

    public function product_infos()
    {
        return $this->hasMany(Products\ProductInfo::class);
    }
















    protected $appends = [
        'sm_logo',
        'md_logo',
        'lg_logo',
    ];

    public function getLgLogoAttribute()
    {
        return $this->logo ? (url('/uploads/brands') . '/' . $this->logo) : null;
    }
    public function getSmLogoAttribute()
    {
        return $this->logo ? (url('/uploads/brands/200') . '/' . $this->logo) : null;
    }
    public function getMdLogoAttribute()
    {
        return $this->logo ? (url('/uploads/brands/600') . '/' . $this->logo) : null;
    }
}

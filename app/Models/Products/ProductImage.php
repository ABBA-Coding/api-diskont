<?php

namespace App\Models\Products;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'img',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_product_image');
    }





    protected $appends = [
        'sm_img',
        'md_img',
        'lg_img',
    ];

    public function getLgImgAttribute()
    {
        return $this->img ? (url('/uploads/products') . '/' . $this->img) : null;
    }
    public function getSmImgAttribute()
    {
        return $this->img ? (url('/uploads/products/200') . '/' . $this->img) : null;
    }
    public function getMdImgAttribute()
    {
        return $this->img ? (url('/uploads/products/600') . '/' . $this->img) : null;
    }
}

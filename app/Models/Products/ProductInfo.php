<?php

namespace App\Models\Products;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Comment;

class ProductInfo extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'for_search',
        'desc',
        'brand_id',
        'category_id',
        'default_product_id',
    ];

    protected $casts = [
        'name' => 'array',
        'desc' => 'array',
    ];

    public function products()
    {
        return $this->hasMany(Product::class, 'info_id');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function default_product()
    {
        return $this->belongsTo(Product::class, 'default_product_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}

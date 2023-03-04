<?php

namespace App\Models\Products;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Attributes\AttributeOption;
use App\Models\Characteristics\CharacteristicOption;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'info_id',
        'c_id',
        'model',
        'price',
        'status',
        'is_popular',
        'product_of_the_day',
    ];

    public function info()
    {
        return $this->belongsTo(ProductInfo::class, 'info_id')->select('id', 'name', 'desc', 'brand_id', 'category_id', 'default_product_id');
    }

    public function images()
    {
        return $this->belongsToMany(ProductImage::class, 'product_product_image');
    }

    public function attribute_options()
    {
        return $this->belongsToMany(AttributeOption::class);
    }

    public function characteristic_options()
    {
        return $this->belongsToMany(CharacteristicOption::class);
    }



    protected $appends = [
        'discount_price',
    ];

    public function getDiscountPriceAttribute()
    {
        return null;
    }
}

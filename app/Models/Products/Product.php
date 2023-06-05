<?php

namespace App\Models\Products;

use App\Models\Showcase;
use App\Models\Attributes\AttributeOption;
use App\Models\Characteristics\CharacteristicOption;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Product extends Model
{
    use HasFactory, Searchable;

    protected $fillable = [
        'info_id',
        'c_id',
        'model',
        'price',
        'status',
        'is_popular',
        'product_of_the_day',
        'is_available',
        'slug',
    ];

    public function info()
    {
        return $this->belongsTo(ProductInfo::class, 'info_id')->select('id', 'name', 'desc', 'brand_id', 'category_id', 'default_product_id', 'is_active');
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

    public function showcases()
    {
        return $this->belongsToMany(Showcase::class);
    }



    protected $appends = [
        'discount_price',
    ];

    public function getDiscountPriceAttribute()
    {
        return null;
    }
}

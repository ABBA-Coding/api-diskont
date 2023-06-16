<?php

namespace App\Models\Products;

use App\Models\Discount;
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

    public function badges()
    {
        return $this->belongsToMany(ProductBadge::class);
    }



    protected $appends = [
        'discount',
    ];

    public function getDiscountAttribute()
    {
        $product_discount = Discount::where([
                ['type', 'product'],
                ['status', 1],
                ['start', '<=', date('Y-m-d')],
            ])
            ->where(function ($q) {
                $q->where('end', null)
                    ->orWhere('end', '>=', date('Y-m-d'));
            })
            ->whereJsonContains('ids', $this->id)
            ->latest()
            ->first();

        if($product_discount) return $product_discount;

        return Discount::where([
                ['type', 'brand'],
                ['status', 1],
                ['start', '<=', date('Y-m-d')]
            ])
            ->where(function ($q) {
                $q->where('end', null)
                    ->orWhere('end', '>=', date('Y-m-d'));
            })
            ->whereJsonContains('ids', $this->info->brand_id)
            ->latest()
            ->first();
    }
}

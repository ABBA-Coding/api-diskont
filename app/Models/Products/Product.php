<?php

namespace App\Models\Products;

use App\Models\ExchangeRate;
use App\Models\Discount;
use App\Models\Showcase;
use App\Models\Promotion;
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
        'installment_price_6',
        'installment_price_12',
        'installment_price_18',
        'installment_price_24',
        'installment_price_36',
        'stock',
        'status',
        'is_popular',
        'product_of_the_day',
        'is_available',
        'slug',
        'dicoin',
//        'name',
//        'desc',
    ];

    protected $appends = [
        'discount',
        'real_price',
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

    public function getRealPriceAttribute()
    {
        return $this->price * ExchangeRate::latest()->first()->exchange_rate;
    }

    public function info()
    {
        return $this->belongsTo(ProductInfo::class, 'info_id')->select('id', 'name', 'desc', 'brand_id', 'category_id', 'default_product_id', 'is_active');
    }

    public function images()
    {
        return $this->belongsToMany(ProductImage::class, 'product_product_image');
    }

    public function attribute_options(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(AttributeOption::class);
    }

    public function characteristic_options(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(CharacteristicOption::class);
    }

    public function showcases(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Showcase::class);
    }

    public function badges(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(ProductBadge::class);
    }

    public function promotions()
    {
        return $this->belongsToMany(Promotion::class);
    }
}

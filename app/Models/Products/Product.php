<?php

namespace App\Models\Products;

use App\Models\ExchangeRate;
use App\Models\Discount;
use App\Models\Showcase;
use App\Models\Branch;
use App\Models\Promotions\Promotion;
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
        'name',
        'for_search',
//        'desc',
    ];

    protected $casts = [
        'name' => 'array',
    ];

    protected $appends = [
        'discount',
        'discount_price',
        'real_price',
    ];

    public function getDiscountAttribute()
    {
        $discounts_count = count($this->discounts);
        $product_discount = isset($this->discounts[0]) ? $this->discounts[$discounts_count - 1] : null;
        // $product_discount = Discount::where([
        //     ['type', 'product'],
        //     ['status', 1],
        //     ['start', '<=', date('Y-m-d')],
        // ])
        //     ->where(function ($q) {
        //         $q->where('end', null)
        //             ->orWhere('end', '>=', date('Y-m-d'));
        //     })
        //     ->latest()
        //     ->first();

        // dd($product_discount);

        if($product_discount) return $product_discount;

        // return Discount::where([
        //     ['type', 'brand'],
        //     ['status', 1],
        //     ['start', '<=', date('Y-m-d')]
        // ])
        //     ->where(function ($q) {
        //         $q->where('end', null)
        //             ->orWhere('end', '>=', date('Y-m-d'));
        //     })
        //     ->whereJsonContains('ids', $this->info->brand_id)
        //     ->latest()
        //     ->first();
    }

    public function getDiscountPriceAttribute()
    {
        $discount_price = null;
        $kurs = ExchangeRate::latest()
            ->first()['exchange_rate'];

        $discount = isset($this->discounts[0]) ? $this->discounts[count($this->discounts) - 1] : null;

        if($discount) {
            if($discount->pivot->percent) {
                return $this->price * (1 - ($discount->pivot->percent / 100)) * $kurs;
            }

            return ($this->price * $kurs) - $discount->pivot->amount;
        }

        return $discount_price;
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
        return $this->belongsToMany(Promotion::class, 'product_promotion', 'product_id', 'promotion_id');
    }

    public function discounts()
    {
        return $this->belongsToMany(Discount::class)->withPivot('percent', 'amount');
    }

    public function translatable(): array
    {
        return [
            'name',
        ];
    }
}

<?php

namespace App\Models\Orders;

use App\Models\Dicoin\DicoinHistory;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\Products\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'delivery_method',
        'name',
        'surname',
        'phone_number',
        'user_address_id',
        'postcode',
        'email',
        'comments',
        'payment_method',
        'products',
        'amount',
        'is_paid',
        'status',
        'delivery_price',
        'req_sent',
        'c_id',
    ];

    protected $casts = [
        'products' => 'array',
    ];

    protected $appends = [
    	'products_info',
        'price_with_dicoin'
    ];

    public function getPriceWithDicoinAttribute()
    {
        return 0;
    }

    public function getProductsInfoAttribute()
    {
    	$products = [];
    	foreach($this->products as $key => $product) {
            $new_arr = $product;

            $new_arr['product'] = Product::with('info', 'images')->find($product['product_id']);

            $products[$key] = $new_arr;
        }
    	return $products;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function dicoin_history()
    {
        return $this->hasOne(DicoinHistory::class);
    }

    public function user_address()
    {
        return $this->belongsTo(UserAddress::class);
    }
}

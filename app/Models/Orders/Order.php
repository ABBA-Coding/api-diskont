<?php

namespace App\Models\Orders;

use App\Models\Dicoin\DicoinHistory;
use App\Models\User;
use App\Models\UserAddress;
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
    ];

    protected $casts = [
        'products' => 'array',
    ];

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

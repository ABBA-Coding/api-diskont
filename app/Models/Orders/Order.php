<?php

namespace App\Models\Orders;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'delivery_method',
        'name',
        'phone_number',
        'region_id',
        'district_id',
        'address',
        'postcode',
        'email',
        'comments',
        'payment_method',
        'products',
        'amount',
    ];

    protected $casts = [
        'products' => 'array',
    ];

    public function client()
    {
        return $this->belongsTo(User::class);
    }
}

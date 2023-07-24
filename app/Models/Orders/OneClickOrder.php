<?php

namespace App\Models\Orders;

use App\Models\Products\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OneClickOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'phone_number',
        'name',
        'count',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

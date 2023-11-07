<?php

namespace App\Models;

use App\Models\Products\ProductInfo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_info_id',
        'comment',
        'stars',
        'is_active',
    ];

    public function product_info()
    {
        return $this->belongsTo(ProductInfo::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

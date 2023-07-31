<?php

namespace App\Models;

use App\Models\Products\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'desc',
        // 'percent',
        // 'amount',
        'type',
        // 'ids',
        'start',
        'end',
        'status',
        'for_search',
    ];

//    protected $hidden = ['ids'];

    protected $casts = [
        'title' => 'array',
        'desc' => 'array',
        'ids' => 'array',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class)->withPivot('percent', 'amount');
    }
}

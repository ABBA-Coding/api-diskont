<?php

namespace App\Models\Products;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductBadge extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'for_search',
    ];

    protected $casts = [
        'name' => 'array'
    ];

    public function products()
    {
    	return $this->belongsToMany(Product::class);
    }
}

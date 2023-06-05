<?php

namespace App\Models;

use App\Models\Products\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Showcase extends Model
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
        return $this->belongsToMany(Product::class)->withPivot('position')->orderBy('position');
    }
}

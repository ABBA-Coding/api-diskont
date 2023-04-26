<?php

namespace App\Models\Attributes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Products\Product;

class AttributeOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'attribute_id',
        'name',
        'for_search',
        'position',
    ];

    protected $casts = [
        'name' => 'array',
    ];

    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class);
    }
}

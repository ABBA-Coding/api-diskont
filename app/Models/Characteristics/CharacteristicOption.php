<?php

namespace App\Models\Characteristics;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Products\Product;

class CharacteristicOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'characteristic_id',
        'name',
        'for_search',
    ];

    protected $casts = [
        'name' => 'array'
    ];

    public function characteristic()
    {
        return $this->belongsTo(Characteristic::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class);
    }
    public function translatable(): array
    {
        return [
            'name',
        ];
    }
}

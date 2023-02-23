<?php

namespace App\Models\Characteristics;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CharacteristicGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'for_search',
    ];

    protected $casts = [
        'name' => 'array'
    ];

    public function characteristics()
    {
        return $this->hasMany(Characteristic::class, 'group_id');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }
}

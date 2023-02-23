<?php

namespace App\Models\Attributes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttributeGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'for_search',
    ];

    protected $casts = [
        'name',
    ];

    public function attributes()
    {
        return $this->hasMany(Attribute::class, 'group_id');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }
}

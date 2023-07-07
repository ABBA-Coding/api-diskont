<?php

namespace App\Models\Attributes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Category;

class Attribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'for_search',
        'keywords',
    ];

    protected $casts = [
        'name' => 'array',
    ];

    public function options()
    {
        return $this->hasMany(AttributeOption::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class)->select('name', 'parent_id', 'is_popular', 'desc', 'icon', 'img');
    }

    public function translatable(): array
    {
        return [
            'name',
        ];
    }
}

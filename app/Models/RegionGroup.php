<?php

namespace App\Models;

use App\Models\Settings\Region;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegionGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'is_active',
        'for_search',
    ];

    protected $casts = [
        'name' => 'array',
    ];

    public function regions()
    {
        return $this->hasMany(Region::class, 'group_id');
    }

    public function translatable(): array
    {
        return [
            'name',
        ];
    }
}

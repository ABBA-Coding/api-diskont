<?php

namespace App\Models\Settings;

use App\Models\User;
use App\Models\RegionGroup;
use App\Models\Branch;
use App\Models\BranchCity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'for_search',
        // 'delivery_price',
        'group_id',
    ];

    protected $casts = [
        'name' => 'array',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function districts()
    {
        return $this->hasMany(District::class);
    }

    public function group()
    {
        return $this->belongsTo(RegionGroup::class, 'group_id');
    }

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    public function branchCity()
    {
        return $this->hasOne(BranchCity::class);
    }

    public function translatable(): array
    {
        return [
            'name',
        ];
    }
}

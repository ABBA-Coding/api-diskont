<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    use HasFactory;

    protected $fillable = [
    	'user_id',
    	'region_id',
    	'district_id',
    	'village_id',
    	'address'
    ];

    protected $appends = [
        'available_for_delivery'
    ];

    public function getAvailableForDeliveryAttribute()
    {
        return $this->region->group_id != null;
    }

    public function user()
    {
    	return $this->belongsTo(User::class);
    }

    public function region()
    {
        return $this->belongsTo(Settings\Region::class);
    }

    public function district()
    {
        return $this->belongsTo(Settings\District::class);
    }

    public function village()
    {
        return $this->belongsTo(Settings\Village::class);
    }
}

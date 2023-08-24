<?php

namespace App\Models;

use App\Models\Settings\Region;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BranchCity extends Model
{
    use HasFactory;

    protected $fillable = [
    	'region_id',
    	'name',
    ];

    public function region()
    {
    	return $this->belongsTo(Region::class);
    }
}

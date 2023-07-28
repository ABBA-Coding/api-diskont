<?php

namespace App\Models\Dicoin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dicoin extends Model
{
    use HasFactory;

    protected $fillable = [
    	'sum_to_dicoin',
    	'dicoin_to_sum',
    	'dicoin_to_reg',
    ];
}

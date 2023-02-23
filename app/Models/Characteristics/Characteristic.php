<?php

namespace App\Models\Characteristics;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Characteristic extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'name',
        'for_search',
    ];

    protected $casts = [
        'name' => 'array'
    ];

    public function group()
    {
        return $this->belongsTo(CharacteristicGroup::class, 'group_id')->select('id', 'name');
    }

    public function options()
    {
        return $this->hasMany(CharacteristicOption::class)->select('id', 'characteristic_id', 'name');
    }
}

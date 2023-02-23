<?php

namespace App\Models\Characteristics;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}

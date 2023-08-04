<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'work_time',
        'phone_number',
        'for_search',
        'region_id',
    ];

    protected $casts = [
        'name' => 'array',
    ];

    public function region()
    {
        return $this->belongsTo(Settings\Region::class);
    }



    public function translatable(): array
    {
        return [
            'name',
        ];
    }
}

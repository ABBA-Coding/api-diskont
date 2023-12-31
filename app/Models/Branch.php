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
        'link',
        'for_search',
        'region_id',
    ];

    protected $casts = [
        'name' => 'array',
        'phone_number' => 'array',
    ];

    public function region()
    {
        return $this->belongsTo(Settings\Region::class);
    }



    public function translatable(): array
    {
        return [
            'name',
            'phone_number',
        ];
    }
}

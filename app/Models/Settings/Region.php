<?php

namespace App\Models\Settings;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'for_search',
        'delivery_price',
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
}

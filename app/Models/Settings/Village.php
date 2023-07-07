<?php

namespace App\Models\Settings;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Village extends Model
{
    use HasFactory;

    protected $fillable = [
        'district_id',
        'name',
        'for_search',
    ];

    protected $casts = [
        'name' => 'array',
    ];

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function translatable(): array
    {
        return [
            'name',
        ];
    }
}

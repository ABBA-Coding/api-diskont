<?php

namespace App\Models\Roles;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'url',
        'methods'
    ];

    protected $casts = [
        'methods' => 'array'
    ];

    public function groups()
    {
        return $this->belongsToMany(PermissionGroup::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }
}

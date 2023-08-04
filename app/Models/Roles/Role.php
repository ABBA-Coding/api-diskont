<?php

namespace App\Models\Roles;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name'
    ];

    public function admin()
    {
        return $this->hasOne(Admin::class);
    }

    public function permission_groups()
    {
        return $this->belongsToMany(PermissionGroup::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }
}

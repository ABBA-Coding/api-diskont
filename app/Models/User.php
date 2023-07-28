<?php

namespace App\Models;

use App\Models\Dicoin\DicoinHistory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'login',
        'password',
        'region_id',
        'district_id',
        'address',
        'postcode',
        'password_updated'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected function username()
    {
        return 'login';
    }

    protected $appends = [
        'dicoin'
    ];

    public function getDicoinAttribute()
    {
        $total = DicoinHistory::where([
                ['user_id', $this->id],
                ['type', 'plus'],
                ['expired_at', null]
            ])
            ->get()
            ->sum('quantity');

        $used = DicoinHistory::where([
                ['user_id', $this->id],
                ['type', 'minus'],
                ['expired_at', null]
            ])
            ->get()
            ->sum('quantity');

        $last = DicoinHistory::where([
                ['user_id', $this->id],
                ['type', 'plus'],
                ['expired_at', null]
            ])
            ->latest()
            ->first();

        return [
            'quantity' => $total - $used,
            'left' => $last ? (180 - ceil(abs(strtotime(date('Y-m-d')) - strtotime($last->created_at)) / 3600 / 24)) : 0
        ];
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function orders()
    {
        return $this->hasMany(Orders\Order::class, 'client_id');
    }

    public function dicoin_history()
    {
        return $this->hasMany(DicoinHistory::class);
    }

    public function addresses()
    {
        return $this->hasMany(UserAddress::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DicoinHistory extends Model
{
    use HasFactory;

    protected $fillable = [
    	'user_id',
    	'type',
    	'order_id',
        'quantity',
        'expired_at',
    ];

    public function user()
    {
    	return $this->belongsTo(User::class);
    }

    public function order()
    {
    	return $this->belongsTo(Orders\Order::class);	
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarabanItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'position',
        'count',
    ];
}

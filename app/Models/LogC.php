<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogC extends Model
{
    use HasFactory;

    protected $fillable = [
        'req',
        'res',
        'body',
    ];
}

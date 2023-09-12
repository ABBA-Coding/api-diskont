<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Info extends Model
{
    use HasFactory;

    protected $fillable = [
        'logo',
        'phone_number',
        'email',
        'telegram',
        'meta_desc',
        'meta_keywords',
    ];

    protected $casts = [
        'meta_desc' => 'array',
        'meta_keywords' => 'array',
    ];

    protected $appends = [
        'sm_logo',
        'md_logo',
        'lg_logo',
    ];

    public function getLgLogoAttribute()
    {
        return $this->logo ? (url('/uploads/info') . '/' . $this->logo) : null;
    }
    public function getSmLogoAttribute()
    {
        return $this->logo ? (url('/uploads/info/200') . '/' . $this->logo) : null;
    }
    public function getMdLogoAttribute()
    {
        return $this->logo ? (url('/uploads/info/600') . '/' . $this->logo) : null;
    }

    public function translatable(): array
    {
        return [
            'meta_desc',
            'meta_keywords',
        ];
    }
}

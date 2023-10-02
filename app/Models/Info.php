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

        'instagram',
        'facebook',
        'favicon',
    ];

    protected $casts = [
        'meta_desc' => 'array',
        'meta_keywords' => 'array',
    ];

    protected $appends = [
        'sm_logo',
        'md_logo',
        'lg_logo',

        'sm_favicon',
        'md_favicon',
        'lg_favicon',
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

    public function getLgFaviconAttribute()
    {
        return $this->favicon ? (url('/uploads/info') . '/' . $this->favicon) : null;
    }
    public function getSmFaviconAttribute()
    {
        return $this->favicon ? (url('/uploads/info/200') . '/' . $this->favicon) : null;
    }
    public function getMdFaviconAttribute()
    {
        return $this->favicon ? (url('/uploads/info/600') . '/' . $this->favicon) : null;
    }

    public function translatable(): array
    {
        return [
            'meta_desc',
            'meta_keywords',
        ];
    }
}

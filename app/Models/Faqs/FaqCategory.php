<?php

namespace App\Models\Faqs;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FaqCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'for_search',
    ];

    protected $casts = [
        'title' => 'array',
    ];

    public function faqs()
    {
        return $this->hasMany(Faq::class, 'category_id');
    }
}

<?php

namespace App\Models\Faqs;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'question',
        'answer',
        'for_search',
    ];

    protected $casts = [
        'question' => 'array',
        'answer' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(FaqCategory::class, 'category_id');
    }

    public function translatable(): array
    {
        return [
            'question',
            'answer',
        ];
    }
}

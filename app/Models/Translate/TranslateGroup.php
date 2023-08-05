<?php

namespace App\Models\Translate;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TranslateGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'sub_text',
        'title',
        'for_search',
    ];

    public function translates(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Translate::class);
    }

    public function translatable(): array
    {
        return [
            'val'
        ];
    }
}

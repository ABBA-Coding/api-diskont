<?php

namespace App\Models\Translate;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Translate extends Model
{
    use HasFactory;

    protected $fillable = [
        'translate_group_id',
        'key',
        'val',
        'for_search',
    ];

    protected $casts = [
        'val' => 'array',
    ];

    public function group(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(TranslateGroup::class, 'translate_group_id');
    }

    public function translatable(): array
    {
        return [
            'val'
        ];
    }
}

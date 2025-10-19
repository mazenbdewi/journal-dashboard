<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageTranslation extends Model
{
    protected $fillable = [
        'page_id', 'locale', 'title', 'slug', 'content',
        'file', 'meta_description', 'keywords',
    ];

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }
}

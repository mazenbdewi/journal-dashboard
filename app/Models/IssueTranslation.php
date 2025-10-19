<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class IssueTranslation extends Model
{
    /** @use HasFactory<\Database\Factories\IssueTranslationFactory> */
    use HasFactory;

    protected $fillable = [
        'issue_id', 'locale', 'title', 'slug', 'description', 'image',
    ];

    public function issue(): BelongsTo
    {
        return $this->belongsTo(Issue::class);
    }

    public function getImageUrlAttribute()
    {
        return $this->image ? Storage::disk('issue')->url($this->image) : null;
    }

    protected static function booted()
    {
        static::deleted(function ($translation) {
            if ($translation->image) {
                Storage::disk('issue')->delete($translation->image);
            }
        });
    }
}

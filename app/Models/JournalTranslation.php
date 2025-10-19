<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class JournalTranslation extends Model
{
    /** @use HasFactory<\Database\Factories\JournalTranslationFactory> */
    use HasFactory;

    protected $fillable = [
        'journal_id', 'locale', 'title', 'slug', 'description', 'image',
    ];

    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }

    public function getImageUrlAttribute()
    {
        return $this->image ? Storage::disk('journal')->url($this->image) : null;
    }

    protected static function booted()
    {
        static::deleted(function ($translation) {
            if ($translation->image) {
                Storage::disk('journal')->delete($translation->image);
            }
        });
    }
}

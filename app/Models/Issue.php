<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Issue extends Model
{
    /** @use HasFactory<\Database\Factories\IssueFactory> */
    use HasFactory;

    protected $fillable = [
        'journal_id', 'volume', 'number', 'year',
        'published_at', 'is_published',
    ];

    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(IssueTranslation::class);
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    public function getCurrentTranslationAttribute()
    {
        return $this->translations
            ->where('locale', app()->getLocale())
            ->first() ?? $this->translations->first();
    }

    public function getCoverUrlAttribute()
    {
        if ($this->currentTranslation && $this->currentTranslation->image) {
            return Storage::disk('issue')->url($this->currentTranslation->image);
        }

        return asset('images/default-cover.png');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeLatestPublished($query)
    {
        return $query->published()->orderBy('published_at', 'desc');
    }

    /**
     * الحصول على العنوان الكامل للعدد
     */
    public function getFullTitleAttribute()
    {
        return "المجلد {$this->volume} - العدد {$this->number} ({$this->year})";
    }

    /**
     * الحصول على عدد المقالات في هذا العدد
     */
    public function getArticlesCountAttribute()
    {
        return $this->articles()->count();
    }

    public function currentTranslation()
    {
        return $this->hasOne(IssueTranslation::class)
            ->where('locale', app()->getLocale());
    }
}

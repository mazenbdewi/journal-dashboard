<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

class Journal extends Model
{
    /** @use HasFactory<\Database\Factories\JournalFactory> */
    use HasFactory;

    protected $fillable = [
        'code', 'issn', 'e_issn', 'name', 'created_by',
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(JournalTranslation::class);
    }

    public function issues(): HasMany
    {
        return $this->hasMany(Issue::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function menu()
    {
        return $this->hasOne(\Datlechin\MenuBuilder\Models\Menu::class);
    }

    public function articles()
    {
        return $this->hasManyThrough(
            Article::class,
            Issue::class,
            'journal_id', // مفتاح وسيط في جدول issues
            'issue_id',   // مفتاح الهدف في جدول articles
            'id',         // مفتاح محلي في جدول journals
            'id'          // مفتاح وسيط في جدول issues
        );
    }

    public function articlesThroughIssues()
    {
        return Article::whereIn('issue_id', $this->issues()->pluck('id'));
    }

    public function getArticlesCountAttribute()
    {
        return $this->articlesThroughIssues()->count();
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
            return Storage::disk('journal')->url($this->currentTranslation->image);
        }

        return asset('/assets/img/logo.png');
    }

    public function scopePublishedIssues($query)
    {
        return $query->whereHas('issues', function ($q) {
            $q->where('is_published', true);
        });
    }

    public function scopeHasIssues($query)
    {
        return $query->whereHas('issues');
    }

    /**
     * الترجمة الحالية حسب اللغة المطلوبة
     */
    public function currentTranslation(): HasOne
    {
        $locale = request()->get('locale', app()->getLocale());

        return $this->hasOne(JournalTranslation::class)->where('locale', $locale);
    }

    // public function getCurrentTranslationAttribute()
    // {
    //     return $this->translations
    //         ->where('locale', app()->getLocale())
    //         ->first() ?? $this->translations->first();
    // }

    // public function getCoverUrlAttribute()
    // {
    //     if ($this->currentTranslation && $this->currentTranslation->image) {
    //         return Storage::disk('journal')->url($this->currentTranslation->image);
    //     }

    //     return asset('images/default-journal.png');
    // }

    // إضافة تحميل التلقائي للعلاقات
    // protected $with = ['currentTranslation'];

    // // إضافة الخصائص التي يجب تضمينها في المصفوفة
    // protected $appends = ['cover_url', 'current_translation'];
}

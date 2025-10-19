<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Page extends Model
{
    protected $fillable = ['active', 'journal_id'];

    public function translations(): HasMany
    {
        return $this->hasMany(PageTranslation::class);
    }

    public function journal(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Journal::class, 'journal_id');
    }

    // ترجمة اسم المجلة حسب اللغة
    public function getJournalTitleAttribute()
    {
        if ($this->journal) {
            return $this->journal->currentTranslation?->title ?? $this->journal->translations->first()?->title;
        }

        return __('page.form.home_page'); // الصفحة الرئيسية
    }
}

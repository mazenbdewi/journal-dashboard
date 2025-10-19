<?php

namespace App\Models;

use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleAuthor extends Model
{
    protected $table = 'article_authors';

    protected $fillable = [
        'user_id',
        'is_main_author',
        'external_name',
        'external_email',
        'external_affiliation',
        'is_registered',
    ];

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // خاصية ديناميكية للحصول على الاسم
    public function getNameAttribute(): string
    {
        return $this->user_id
            ? $this->user->name
            : ($this->external_name ?? 'Unknown Author');
    }

    protected static function booted()
    {
        static::deleting(function ($author) {
            if ($author->is_main_author) {
                Notification::make()
                    ->title('لا يمكن حذف المؤلف الرئيسي')
                    ->danger()
                    ->body('يرجى تعيين مؤلف آخر كمؤلف رئيسي أولاً قبل الحذف.')
                    ->send();

                return false;
            }
        });
    }
}

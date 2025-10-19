<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'issue_id', 'doi', 'status',
        'published_at', 'created_by',
    ];

    protected $appends = ['status_label', 'dual_title', 'main_author_display'];

    public function issue(): BelongsTo
    {
        return $this->belongsTo(Issue::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(ArticleTranslation::class);
    }

    // public function authors(): BelongsToMany
    // {
    //     return $this->belongsToMany(User::class, 'article_authors')
    //         ->withPivot('is_main_author');
    // }

    // public function authors(): BelongsToMany
    // {
    //     return $this->belongsToMany(User::class, 'article_authors')
    //         ->using(ArticleAuthor::class)
    //         ->withPivot([
    //             'external_name',
    //             'external_email',
    //             'external_affiliation',
    //             'is_main_author',
    //         ]);
    // }

    // public function authors(): BelongsToMany
    // {
    //     return $this->belongsToMany(User::class, 'article_authors')
    //         ->using(ArticleAuthor::class)
    //         ->withPivot(['external_name', 'external_email', 'external_affiliation', 'is_main_author']);
    // }
    public function articleAuthors(): HasMany
    {
        return $this->hasMany(ArticleAuthor::class);
    }

    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'article_authors')
            ->using(ArticleAuthor::class)
            ->withPivot([
                'external_name',
                'external_email',
                'external_affiliation',
                'is_main_author',
            ]);
    }

    public function getMainAuthorDisplayAttribute(): string
    {
        $mainAuthor = $this->articleAuthors()
            ->with('user')
            ->where('is_main_author', true)
            ->first();

        if (! $mainAuthor) {
            $mainAuthor = $this->articleAuthors()->with('user')->first();
        }

        if (! $mainAuthor) {
            return 'بدون مؤلف';
        }

        return $mainAuthor->user?->name
            ?? $mainAuthor->external_name
            ?? 'بدون اسم';
    }

    public function mainAuthor(): User|array
    {
        $mainAuthor = $this->authors->where('pivot.is_main_author', true)->first();

        return $mainAuthor ?: [
            'name' => $this->authors->first()->pivot->external_name ?? 'Unknown',
            'email' => $this->authors->first()->pivot->external_email ?? '',
            'affiliation' => $this->authors->first()->pivot->external_affiliation ?? '',
        ];
    }

    public function scopeWithMainAuthor(Builder $query): Builder
    {
        return $query->with(['authors' => function ($q) {
            $q->where('is_main_author', true);
        }]);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'مسودة',
            'under_review' => 'قيد المراجعة',
            'accepted' => 'مقبول',
            'published' => 'منشور',
            'rejected' => 'مرفوض',
            'revoke' => 'منسحب',
            default => 'غير معروف'
        };
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(ArticleRevision::class);
    }

    // علاقة جديدة للتعامل مع ملفات المقال
    public function files(): HasMany
    {
        return $this->hasMany(ArticleFile::class);
    }

    public function reviewAssignments(): HasMany
    {
        return $this->hasMany(ReviewAssignment::class);
    }

    public function getTitleAttribute(): ?string
    {
        return $this->translations->first()?->title;
    }

    public function scopeAssignedToReviewer(Builder $query, int $userId): Builder
    {
        return $query->whereHas('reviewAssignments', function ($q) use ($userId) {
            $q->where('reviewer_id', $userId);
        });
    }

    public function scopeAcceptedOrPublished(Builder $query): Builder
    {
        return $query->whereIn('status', ['accepted', 'published']);
    }

    public function isAssignedTo(int $reviewerId): bool
    {
        return $this->reviewAssignments()
            ->where('reviewer_id', $reviewerId)
            ->exists();
    }

    public function getAllTranslations(): array
    {
        $translations = [];
        foreach ($this->translations as $translation) {
            $translations[$translation->locale] = [
                'title' => $translation->title,
                'abstract' => $translation->abstract,
            ];
        }

        return $translations;
    }

    public function getTitle(string $locale): string
    {
        // الطريقة المثلى لجلب الترجمة
        $translation = $this->translations()
            ->where('locale', $locale)
            ->first();

        return $translation->title ?? 'بدون عنوان';
    }

    public function getArabicTitleAttribute(): ?string
    {
        return $this->getTranslation('ar')?->title;
    }

    public function getEnglishTitleAttribute(): ?string
    {
        return $this->getTranslation('en')?->title;
    }

    public function getDualTitleAttribute(): string
    {
        $ar = $this->arabic_title ?? 'بدون عنوان';
        $en = $this->english_title ?? 'No title';

        return "العنوان (عربي): $ar - (إنجليزي): $en";
    }

    // دالة مساعدة للحصول على الترجمة
    public function getTranslation(string $locale): ?ArticleTranslation
    {
        // تحميل الترجمات إذا لم تكن محملة
        if (! $this->relationLoaded('translations')) {
            $this->load('translations');
        }

        return $this->translations->firstWhere('locale', $locale);
    }

    // دالة لمعالجة الترجمات
    public function processTranslations(array $data): void
    {
        $locales = ['en', 'ar'];

        foreach ($locales as $locale) {
            $translationData = [
                'title' => $data["{$locale}_title"] ?? 'Untitled',
                'abstract' => $data["{$locale}_abstract"] ?? '',
                'content' => $data["{$locale}_content"] ?? '',
                'keywords' => $data["{$locale}_keywords"] ?? '',
            ];

            $this->translations()->updateOrCreate(
                ['locale' => $locale],
                $translationData
            );
        }
    }

    // دالة لتحميل الترجمات مع المقال
    public function scopeWithTranslations($query)
    {
        return $query->with(['translations']);
    }

    protected static function booted()
    {
        static::creating(function ($article) {
            $article->submission_at = now();
        });
        static::updated(function (Article $article) {
            if ($article->isDirty('status') && $article->status === 'revoke') {
                $article->sendWithdrawalNotifications();
            }
            if ($article->isDirty('status') && $article->status === 'published') {
                $article->sendPublishedNotifications();
            }
        });

    }

    public function getDualTitle(): string
    {
        // تأكد من وجود الترجمات المحملة
        if (! $this->relationLoaded('translations')) {
            $this->load(['translations' => fn ($q) => $q->whereIn('locale', ['ar', 'en'])]);
        }

        $arTitle = $this->translations->firstWhere('locale', 'ar')->title ?? 'بدون عنوان';
        $enTitle = $this->translations->firstWhere('locale', 'en')->title ?? 'No Title';

        return "$arTitle ($enTitle)";
    }

    public function getEnglishTitle(): string
    {
        // تأكد من وجود الترجمات المحملة
        if (! $this->relationLoaded('translations')) {
            $this->load(['translations' => fn ($q) => $q->whereIn('locale', ['en'])]);
        }

        $enTitle = $this->translations->firstWhere('locale', 'en')->title ?? 'No Title';

        return "$enTitle";
    }

    public function journal()
    {
        return $this->hasOneThrough(
            Journal::class,
            Issue::class,
            'id',           // المفتاح المحلي في النموذج الوسيط (Issue)
            'id',           // المفتاح المحلي في النموذج الهدف (Journal)
            'issue_id',     // المفتاح الخارجي في النموذج الحالي
            'journal_id'    // المفتاح الخارجي في النموذج الوسيط
        );
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at');
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    /**
     * نطاق الاستعلام للحصول على المقالات قيد المراجعة
     */
    public function scopeUnderReview($query)
    {
        return $query->where('status', 'under_review');
    }

    /**
     * نطاق الاستعلام للترتيب حسب تاريخ النشر
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('published_at', 'desc');
    }
    // داخل App\Models\Article.php

    public function currentTranslation()
    {
        $locale = app()->getLocale(); // يمكنك تغييره حسب الطلب

        return $this->hasOne(ArticleTranslation::class)
            ->where('locale', $locale);
    }

    public function sendWithdrawalNotifications(): void
    {
        $users = [];

        // المشرفون
        $superAdmins = \App\Models\User::whereHas('roles', function ($q) {
            $q->where('name', 'super_admin');
        })->get();

        foreach ($superAdmins as $admin) {
            $users[] = $admin;
        }

        // المحكمين
        $reviewers = $this->reviewAssignments()->with('reviewer')->get()->pluck('reviewer');
        foreach ($reviewers as $reviewer) {
            if ($reviewer) {
                $users[] = $reviewer;
            }
        }

        // إرسال الإشعار للجميع
        foreach ($users as $user) {
            try {
                $user->notify(new \App\Notifications\ArticleWithdrawnNotification($this));
            } catch (\Exception $e) {
                \Log::error("فشل إرسال إشعار سحب المقالة للمستخدم {$user->id}: ".$e->getMessage());
            }
        }
    }

    public function sendPublishedNotifications(): void
    {
        // الحصول على المؤلفين المسجلين
        $mainAuthor = $this->articleAuthors()->with('user')->where('is_main_author', true)->first();

        $users = [];
        if ($mainAuthor?->user) {
            $users[] = $mainAuthor->user;
        }

        foreach ($users as $user) {
            try {
                // إرسال إشعار عبر Notification
                $user->notify(new \App\Notifications\ArticlePublishedNotification($this));
            } catch (\Exception $e) {
                \Log::error("فشل إرسال إشعار نشر المقالة للمستخدم {$user->id}: ".$e->getMessage());
            }
        }
    }
}

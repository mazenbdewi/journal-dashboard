<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Log;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'article_id',
        'reviewer_id',
        'review_date',
        'status',
        'decision',
        'editor_notes',
    ];

    // علاقة بالمقال
    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    // علاقة بالمحكم (مراجع)
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    // علاقة باستمارة التقييم
    public function evaluation(): HasOne
    {
        return $this->hasOne(ReviewEvaluation::class);
    }

    // علاقة بمراجعات التعديل
    public function revisions(): HasMany
    {
        return $this->hasMany(ReviewRevision::class);
    }

    // علاقة جديدة للحصول على التقييم بشكل آمن
    public function safeEvaluation(): HasOne
    {
        return $this->evaluation()->withDefault();
    }

    protected static function booted()
    {
        static::saving(function (Review $review) {
            // التحقق من عدم وجود مراجعة أخرى لنفس المقالة
            $existingReview = Review::where('article_id', $review->article_id)
                ->when($review->exists, function ($query) use ($review) {
                    $query->where('id', '!=', $review->id);
                })
                ->exists();

            if ($existingReview) {
                throw new \Exception('هذه المقالة لها مراجعة أساسية بالفعل!');
            }
        });

        // إرسال الإشعارات عند الإنشاء إذا كان هناك قرار
        static::created(function (Review $review) {
            if (! empty($review->decision)) {
                Log::info('مراجعة جديدة تم إنشاؤها مع قرار', [
                    'review_id' => $review->id,
                    'decision' => $review->decision,
                ]);
                $review->sendDecisionNotifications('created');
            }
        });

        // إرسال الإشعارات عند التحديث إذا تغير القرار
        static::updated(function (Review $review) {
            if ($review->isDirty('decision') && ! empty($review->decision)) {
                Log::info('تم تحديث قرار المراجعة', [
                    'review_id' => $review->id,
                    'old_decision' => $review->getOriginal('decision'),
                    'new_decision' => $review->decision,
                ]);
                $review->sendDecisionNotifications('updated');
            }
        });
    }

    /**
     * إرسال الإشعارات عند تحديث القرار
     */
    public function sendDecisionNotifications(string $action = 'updated'): void
    {
        try {
            $usersToNotify = $this->getUsersToNotify();

            Log::info('المستخدمون الذين سيتم إشعارهم', [
                'review_id' => $this->id,
                'users_count' => count($usersToNotify),
                'users' => array_map(function ($user) {
                    return ['id' => $user->id, 'name' => $user->name, 'email' => $user->email];
                }, $usersToNotify),
            ]);

            foreach ($usersToNotify as $user) {
                try {
                    $user->notify(new \App\Notifications\ReviewDecisionUpdated($this, $action));
                    Log::info('تم إرسال الإشعار للمستخدم', [
                        'user_id' => $user->id,
                        'user_email' => $user->email,
                    ]);
                } catch (\Exception $e) {
                    Log::error('فشل إرسال الإشعار للمستخدم: '.$e->getMessage(), [
                        'user_id' => $user->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

        } catch (\Exception $e) {
            Log::error('فشل في إرسال إشعارات قرار المراجعة: '.$e->getMessage(), [
                'review_id' => $this->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * الحصول على المستخدمين الذين يجب إشعارهم
     */
    private function getUsersToNotify(): array
    {
        $users = [];

        try {
            // إشعار المؤلف الرئيسي للمقالة (من خلال جدول article_authors)
            if ($this->article) {
                $mainAuthor = $this->getMainAuthor();
                if ($mainAuthor) {
                    $users[] = $mainAuthor;
                    Log::info('تم إضافة المؤلف الرئيسي للإشعار', [
                        'article_id' => $this->article->id,
                        'user_id' => $mainAuthor->id,
                        'user_email' => $mainAuthor->email,
                    ]);
                } else {
                    Log::warning('لم يتم العثور على المؤلف الرئيسي للمقال', [
                        'article_id' => $this->article->id,
                    ]);
                }
            } else {
                Log::warning('لم يتم العثور على المقالة', [
                    'article_id' => $this->article_id,
                ]);
            }

            // إشعار المشرفين (super_admin)
            $superAdmins = \App\Models\User::whereHas('roles', function ($query) {
                $query->where('name', 'super_admin');
            })->get();

            Log::info('عدد المشرفين الذين سيتم إشعارهم', [
                'super_admins_count' => $superAdmins->count(),
            ]);

            foreach ($superAdmins as $admin) {
                $users[] = $admin;
            }

        } catch (\Exception $e) {
            Log::error('خطأ في الحصول على المستخدمين للإشعار: '.$e->getMessage());
        }

        // إزالة التكرارات
        $uniqueUsers = [];
        $userIds = [];

        foreach ($users as $user) {
            if ($user && ! in_array($user->id, $userIds)) {
                $uniqueUsers[] = $user;
                $userIds[] = $user->id;
            }
        }

        Log::info('العدد النهائي للمستخدمين المطلوب إشعارهم', [
            'total_users' => count($uniqueUsers),
        ]);

        return $uniqueUsers;
    }

    /**
     * الحصول على المؤلف الرئيسي للمقال
     */
    private function getMainAuthor(): ?User
    {
        try {
            // تحميل العلاقة مع articleAuthors و user
            $this->load(['article.articleAuthors.user']);

            // البحث عن المؤلف الرئيسي
            $mainAuthorPivot = $this->article->articleAuthors
                ->where('is_main_author', true)
                ->first();

            if ($mainAuthorPivot && $mainAuthorPivot->user) {
                return $mainAuthorPivot->user;
            }

            // إذا لم يكن هناك مؤلف رئيسي، نأخذ أول مؤلف
            $firstAuthorPivot = $this->article->articleAuthors->first();
            if ($firstAuthorPivot && $firstAuthorPivot->user) {
                return $firstAuthorPivot->user;
            }

            return null;

        } catch (\Exception $e) {
            Log::error('خطأ في الحصول على المؤلف الرئيسي: '.$e->getMessage());

            return null;
        }
    }

    /**
     * التحقق إذا كان يمكن تعديل المراجعة
     */
    public function canBeEdited(): bool
    {
        return in_array($this->status, ['pending', 'under_review']);
    }

    /**
     * الحصول على نص القرار بالعربية
     */
    public function getDecisionText(): string
    {
        $decisions = [
            'accept' => 'قبول',
            'minor_revision' => 'تعديل طفيف',
            'major_revision' => 'تعديل جوهري',
            'reject' => 'رفض',
            'withdrawn' => 'سحب',
        ];

        return $decisions[$this->decision] ?? $this->decision;
    }

    /**
     * الحصول على نص القرار بالإنجليزية
     */
    public function getDecisionTextEn(): string
    {
        $decisions = [
            'accept' => 'Accept',
            'minor_revision' => 'Minor Revision',
            'major_revision' => 'Major Revision',
            'reject' => 'Reject',
            'withdrawn' => 'Withdrawn',
        ];

        return $decisions[$this->decision] ?? $this->decision;
    }
}

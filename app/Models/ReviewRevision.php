<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class ReviewRevision extends Model
{
    use HasFactory;

    protected $fillable = [
        'review_id',
        'revision_status',
        'revision_date',
        'notes_for_author',
        'notes_for_editor',
        'file_path',
        'note',
    ];

    protected $casts = [
        'revision_date' => 'date',
    ];

    // علاقة بالمراجعة الأصلية
    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }

    protected static function booted()
    {
        // إرسال الإشعارات عند الإنشاء
        static::created(function (ReviewRevision $revision) {
            Log::info('تم إنشاء مراجعة جديدة', [
                'revision_id' => $revision->id,
                'status' => $revision->revision_status,
            ]);
            $revision->sendStatusNotifications('created');
        });

        // إرسال الإشعارات عند التحديث إذا تغيرت الحالة
        static::updated(function (ReviewRevision $revision) {
            if ($revision->isDirty('revision_status')) {
                Log::info('تم تحديث حالة المراجعة', [
                    'revision_id' => $revision->id,
                    'old_status' => $revision->getOriginal('revision_status'),
                    'new_status' => $revision->revision_status,
                ]);
                $revision->sendStatusNotifications('updated');
            }
        });
    }

    /**
     * إرسال الإشعارات عند تغيير الحالة
     */
    public function sendStatusNotifications(string $action = 'updated'): void
    {
        try {
            $usersToNotify = $this->getUsersToNotify();

            Log::info('المستخدمون الذين سيتم إشعارهم للمراجعة', [
                'revision_id' => $this->id,
                'users_count' => count($usersToNotify),
            ]);

            foreach ($usersToNotify as $user) {
                try {
                    $user->notify(new \App\Notifications\RevisionStatusUpdated($this, $action));
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
            Log::error('فشل في إرسال إشعارات حالة المراجعة: '.$e->getMessage(), [
                'revision_id' => $this->id,
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
            // تحميل العلاقات المطلوبة
            $this->load(['review.article.articleAuthors.user', 'review.reviewer']);

            // إشعار المؤلف الرئيسي للمقالة
            $mainAuthor = $this->getMainAuthor();
            if ($mainAuthor) {
                $users[] = $mainAuthor;
                Log::info('تم إضافة المؤلف الرئيسي للإشعار', [
                    'author_id' => $mainAuthor->id,
                ]);
            }

            // إشعار المحكم (مراجع المقالة)
            if ($this->review->reviewer) {
                $users[] = $this->review->reviewer;
            }

            // إشعار المشرفين (super_admin)
            $superAdmins = \App\Models\User::whereHas('roles', function ($query) {
                $query->where('name', 'super_admin');
            })->get();

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

        return $uniqueUsers;
    }

    /**
     * الحصول على المؤلف الرئيسي للمقال
     */
    private function getMainAuthor(): ?\App\Models\User
    {
        try {
            // البحث عن المؤلف الرئيسي المسجل (له user_id)
            $mainAuthorPivot = $this->review->article->articleAuthors
                ->where('is_main_author', true)
                ->whereNotNull('user_id')
                ->first();

            if ($mainAuthorPivot && $mainAuthorPivot->user) {
                return $mainAuthorPivot->user;
            }

            // إذا لم يكن هناك مؤلف رئيسي مسجل، نبحث عن أي مؤلف مسجل
            $registeredAuthorPivot = $this->review->article->articleAuthors
                ->whereNotNull('user_id')
                ->first();

            if ($registeredAuthorPivot && $registeredAuthorPivot->user) {
                return $registeredAuthorPivot->user;
            }

            return null;

        } catch (\Exception $e) {
            Log::error('خطأ في الحصول على المؤلف الرئيسي: '.$e->getMessage());

            return null;
        }
    }

    // دالة للحصول على نص الحالة
    public function getStatusText(): string
    {
        $statuses = [
            'done' => 'مكتمل',
            'partially_done' => 'تم الإنجاز جزئياً',
            'not_done' => 'غير مكتمل',
        ];

        return $statuses[$this->revision_status] ?? $this->revision_status;
    }

    public function getStatusTextEn(): string
    {
        $statuses = [
            'done' => 'Done',
            'partially_done' => 'Partially Done',
            'not_done' => 'Not Done',
        ];

        return $statuses[$this->revision_status] ?? $this->revision_status;
    }
}

<?php

namespace App\Notifications;

use App\Models\Review;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class ReviewDecisionUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Review $review, public string $action = 'updated')
    {
        Log::info('إنشاء إشعار جديد', [
            'review_id' => $review->id,
            'action' => $action,
        ]);
    }

    public function via(mixed $notifiable): array
    {
        Log::info('تحديد قنوات الإشعار للمستخدم', [
            'user_id' => $notifiable->id,
            'user_email' => $notifiable->email ?? null,
        ]);

        return ['database', 'mail'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        try {
            $this->review->loadMissing(['article.articleAuthors.user', 'reviewer']);

            $articleTitle = $this->review->article->getDualTitle() ?? 'عنوان غير محدد';
            $mainAuthor = $this->getMainAuthorName();
            $decisionTextAr = $this->translateDecisionAr($this->review->decision ?? '');
            $decisionTextEn = $this->translateDecisionEn($this->review->decision ?? '');
            $reviewerName = $this->review->reviewer?->name ?? 'غير معروف';
            $reviewDate = $this->review->updated_at instanceof \DateTime
                ? $this->review->updated_at->format('Y-m-d H:i')
                : now()->format('Y-m-d H:i');
            $url = URL::to("/adminpanel/reviews/{$this->review->id}/edit");

            $actionTextAr = $this->action === 'created' ? 'تم إنشاء قرار مراجعة جديد' : 'تم تحديث قرار المراجعة';
            $actionTextEn = $this->action === 'created' ? 'New review decision created' : 'Review decision updated';

            return (new MailMessage)
                ->subject("{$actionTextEn} - {$actionTextAr}")
                ->view('emails.review_decision_update', [
                    'name' => $notifiable->name,
                    'articleTitle' => $articleTitle,
                    'mainAuthor' => $mainAuthor,
                    'decisionTextAr' => $decisionTextAr,
                    'decisionTextEn' => $decisionTextEn,
                    'reviewerName' => $reviewerName,
                    'reviewDate' => $reviewDate,
                    'url' => $url,
                    'actionTextAr' => $actionTextAr,
                    'actionTextEn' => $actionTextEn,
                ]);

        } catch (\Exception $e) {
            Log::error('فشل في تحضير البريد الإلكتروني لإشعار القرار: '.$e->getMessage());

            return (new MailMessage)
                ->subject('Review Decision Update')
                ->line('There was an issue generating the detailed email. Please check the system.')
                ->action('Go to Reviews', URL::to('/adminpanel/reviews'))
                ->line('Thank you for using our system.');
        }
    }

    public function toArray(mixed $notifiable): array
    {
        try {
            $this->review->loadMissing(['article.articleAuthors.user', 'reviewer']);

            $articleTitle = $this->review->article->getDualTitle() ?? 'عنوان غير محدد';
            $mainAuthor = $this->getMainAuthorName();
            $decisionTextAr = $this->translateDecisionAr($this->review->decision ?? '');
            $decisionTextEn = $this->translateDecisionEn($this->review->decision ?? '');
            $reviewerName = $this->review->reviewer?->name ?? 'غير معروف';
            $reviewDate = $this->review->updated_at instanceof \DateTime
                ? $this->review->updated_at->toISOString()
                : now()->toISOString();

            $actionTextAr = $this->action === 'created' ? 'تم إنشاء قرار مراجعة جديد' : 'تم تحديث قرار المراجعة';

            return [
                'review_id' => $this->review->id,
                'article_id' => $this->review->article_id,
                'article_title' => $articleTitle,
                'decision' => $this->review->decision,
                'decision_text_ar' => $decisionTextAr,
                'decision_text_en' => $decisionTextEn,
                'main_author_name' => $mainAuthor,
                'reviewer_name' => $reviewerName,
                'review_date' => $reviewDate,
                'action' => $this->action,
                'message' => "{$actionTextAr} للمقالة: {$articleTitle} إلى: {$decisionTextAr}",
                'message_en' => "Review decision {$this->action} for article: {$articleTitle} to: {$decisionTextEn}",
                'url' => "/adminpanel/reviews/{$this->review->id}/edit",
                'type' => 'review_decision_'.$this->action,
                'timestamp' => now()->toISOString(),
            ];

        } catch (\Exception $e) {
            Log::error('فشل في تحضير إشعار قاعدة البيانات: '.$e->getMessage());

            return [
                'error' => 'فشل في تحضير بيانات الإشعار',
                'review_id' => $this->review->id,
                'timestamp' => now()->toISOString(),
            ];
        }
    }

    private function getMainAuthorName(): string
    {
        try {
            $this->review->article->loadMissing('articleAuthors.user');

            $mainAuthor = $this->review->article->articleAuthors
                ->where('is_main_author', true)
                ->first();

            if ($mainAuthor) {
                if ($mainAuthor->user) {
                    return $mainAuthor->user->name;
                } elseif ($mainAuthor->external_name) {
                    return $mainAuthor->external_name.' (مؤلف خارجي)';
                }
            }

            $anyAuthor = $this->review->article->articleAuthors->first();
            if ($anyAuthor) {
                if ($anyAuthor->user) {
                    return $anyAuthor->user->name;
                } elseif ($anyAuthor->external_name) {
                    return $anyAuthor->external_name.' (مؤلف خارجي)';
                }
            }

            return 'مؤلف غير معروف';

        } catch (\Exception $e) {
            Log::error('فشل في الحصول على اسم المؤلف الرئيسي: '.$e->getMessage());

            return 'مؤلف غير معروف';
        }
    }

    private function translateDecisionAr(string $decision): string
    {
        return match (strtolower($decision)) {
            'accept' => 'مقبول',
            'minor_revision' => 'تعديل طفيف',
            'major_revision' => 'تعديل جوهري',
            'reject' => 'مرفوض',
            default => $decision,
        };
    }

    private function translateDecisionEn(string $decision): string
    {
        return match (strtolower($decision)) {
            'accept' => 'Accepted',
            'minor_revision' => 'Minor Revision',
            'major_revision' => 'Major Revision',
            'reject' => 'Rejected',
            default => $decision,
        };
    }
}

<?php

namespace App\Notifications;

use App\Models\ReviewRevision;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class RevisionStatusUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public ReviewRevision $revision, public string $action = 'updated')
    {
        Log::info('🎯 إنشاء إشعار حالة مراجعة جديد', [
            'revision_id' => $revision->id,
            'action' => $action,
            'status' => $revision->revision_status,
        ]);
    }

    public function via(object $notifiable): array
    {
        Log::info('📡 تحديد قنوات الإشعار للمستخدم', [
            'user_id' => $notifiable->id,
            'user_email' => $notifiable->email,
        ]);

        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        Log::info('📧 تحضير البريد الإلكتروني لإشعار تحديث حالة المراجعة', [
            'user_id' => $notifiable->id,
            'email' => $notifiable->email,
        ]);

        return (new MailMessage)
            ->subject(__('Review Status Updated'))
            ->view('emails.revision_status', [
                'name' => $notifiable->name,
                'articleTitle' => $this->revision->review->article->getDualTitle(),
                'statusText' => $this->revision->getStatusText(),
                'statusTextEn' => $this->revision->getStatusTextEn(),
                'timestamp' => now()->toDateTimeString(),
                'actionText' => $this->action === 'created' ? __('New Review Status') : __('Review Status Updated'),
                'actionAr' => $this->action === 'created' ? 'إنشاء' : 'تحديث',
                'actionEn' => $this->action === 'created' ? 'created' : 'updated',
                'reviewId' => $this->revision->review_id,
            ]);
    }

    public function toDatabase(object $notifiable): array
    {
        Log::info('💾 بدء حفظ الإشعار في قاعدة البيانات للمستخدم', [
            'user_id' => $notifiable->id,
        ]);

        try {
            if (! $this->revision->relationLoaded('review') ||
                ! $this->revision->review->relationLoaded('article')) {
                $this->revision->load(['review.article.translations']);
            }

            $articleTitle = $this->revision->review->article->getDualTitle() ?? 'عنوان غير محدد';
            $status = $this->revision->revision_status;
            $statusText = $this->revision->getStatusText();

            $actionText = $this->action === 'created' ? 'تم إنشاء حالة مراجعة جديدة' : 'تم تحديث حالة المراجعة';

            return [
                'revision_id' => $this->revision->id,
                'review_id' => $this->revision->review_id,
                'article_id' => $this->revision->review->article_id,
                'article_title' => $articleTitle,
                'status' => $status,
                'status_text' => $statusText,
                'action' => $this->action,
                'message' => "{$actionText} للمقالة: {$articleTitle} إلى: {$statusText}",
                'message_en' => "Revision status {$this->action} for article: {$articleTitle} to: {$this->revision->getStatusTextEn()}",
                'url' => '/admin/reviews/'.$this->revision->review_id.'/edit',
                'type' => 'revision_status_'.$this->action,
                'timestamp' => now()->toISOString(),
            ];

        } catch (\Exception $e) {
            Log::error('❌ فشل في تحضير إشعار قاعدة البيانات: '.$e->getMessage());

            return [
                'error' => 'فشل في تحضير بيانات الإشعار',
                'revision_id' => $this->revision->id,
                'message' => 'حدث خطأ في تحضير الإشعار',
                'timestamp' => now()->toISOString(),
            ];
        }
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}

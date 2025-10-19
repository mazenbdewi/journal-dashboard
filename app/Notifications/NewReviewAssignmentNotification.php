<?php

namespace App\Notifications;

use App\Models\ReviewAssignment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class NewReviewAssignmentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public ReviewAssignment $reviewAssignment) {}

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'message' => 'تم تعيينك لمراجعة مقال جديد: "'.($this->reviewAssignment->article->title ?? 'بدون عنوان').'"',
            'title' => 'مهمة مراجعة جديدة',
            'article_title' => $this->reviewAssignment->article->title ?? 'بدون عنوان',
            'type' => 'review_assignment',
            'url' => $this->getSafeUrl(),
            'icon' => 'heroicon-o-document-text',
            'timestamp' => now()->toISOString(),
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'message' => 'تم تعيينك لمراجعة مقال جديد: "'.($this->reviewAssignment->article->title ?? 'بدون عنوان').'"',
            'title' => 'مهمة مراجعة جديدة',
            'article_title' => $this->reviewAssignment->article->title ?? 'بدون عنوان',
            'type' => 'review_assignment',
            'url' => $this->getSafeUrl(),
            'icon' => 'heroicon-o-document-text',
            'timestamp' => now()->toISOString(),
        ]);
    }

    private function getSafeUrl(): string
    {
        try {
            return route('filament.adminpanel.resources.review-assignments.index');
        } catch (\Exception $e) {
            return route('filament.adminpanel.pages.dashboard');
        }
    }
}

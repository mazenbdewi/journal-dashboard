<?php

namespace App\Notifications;

use App\Models\ReviewAssignment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ReviewerDecisionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $reviewAssignment;

    protected $decision;

    /**
     * Create a new notification instance.
     */
    public function __construct(ReviewAssignment $reviewAssignment, string $decision)
    {
        $this->reviewAssignment = $reviewAssignment;
        $this->decision = $decision;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation for database storage.
     */
    public function toArray(object $notifiable): array
    {
        $articleTitle = $this->reviewAssignment->article->title ?? 'No Title';
        $reviewerName = $this->reviewAssignment->reviewer->name ?? 'Unknown Reviewer';

        $arabicMessage = $this->decision === 'accepted'
            ? "قام المحكم {$reviewerName} بقبول طلب مراجعة المقالة: {$articleTitle}"
            : "قام المحكم {$reviewerName} برفض طلب مراجعة المقالة: {$articleTitle}";

        $englishMessage = $this->decision === 'accepted'
            ? "Reviewer {$reviewerName} has accepted the review assignment for article: {$articleTitle}"
            : "Reviewer {$reviewerName} has declined the review assignment for article: {$articleTitle}";

        return [
            'review_assignment_id' => $this->reviewAssignment->id,
            'article_id' => $this->reviewAssignment->article_id,
            'article_title' => $articleTitle,
            'reviewer_id' => $this->reviewAssignment->reviewer_id,
            'reviewer_name' => $reviewerName,
            'decision' => $this->decision,
            'message_ar' => $arabicMessage,
            'message_en' => $englishMessage,
            'url' => '/adminpanel/review-assignments/'.$this->reviewAssignment->id,
            'type' => 'reviewer_decision',
            'icon' => $this->decision === 'accepted' ? '✅' : '❌',
            'created_at' => now()->toDateTimeString(),
        ];
    }
}

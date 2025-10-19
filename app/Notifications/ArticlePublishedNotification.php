<?php

namespace App\Notifications;

use App\Models\Article;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class ArticlePublishedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Article $article;

    public function __construct(Article $article)
    {
        $this->article = $article;

        // مثال على تأخير الإرسال لمدة دقيقة واحدة
        $this->delay(now()->addMinute());
        Log::info("تم جدولة إشعار نشر المقالة #{$article->id}");
    }

    public function via($notifiable)
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail($notifiable)
    {
        \Log::info('تم استدعاء toMail في ArticlePublishedNotification للمستخدم: '.$notifiable->email);

        return (new MailMessage)
            ->subject('تم نشر المقالة | Article Published')
            ->view('emails.article_published', [
                'actionText' => 'Article Published',
                'name' => $notifiable->name,
                'articleTitle' => $this->article->dual_title,
                'mainAuthor' => $this->article->mainAuthorDisplay,
                'publishDate' => $this->article->published_at?->format('d-m-Y'),
                'url' => url("/adminpanel/articles/{$this->article->id}/edit"),
            ]);
    }

    public function toDatabase($notifiable)
    {
        return [
            'article_id' => $this->article->id,
            'title' => $this->article->dual_title,
            'message' => "تم نشر المقالة: {$this->article->dual_title}",
            'message_en' => "Article published: {$this->article->dual_title}",
            'type' => 'article_published',
        ];
    }

    public function toBroadcast($notifiable)
    {
        return [
            'article_id' => $this->article->id,
            'title' => $this->article->dual_title,
            'message' => "تم نشر المقالة: {$this->article->dual_title}",
            'message_en' => "Article published: {$this->article->dual_title}",
            'type' => 'article_published',
        ];
    }
}

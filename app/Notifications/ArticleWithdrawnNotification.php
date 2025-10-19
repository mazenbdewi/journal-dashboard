<?php

namespace App\Notifications;

use App\Models\Article;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class ArticleWithdrawnNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Article $article) {}

    public function via(mixed $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('سحب مقال - Article Withdrawn')
            ->view('emails.article_withdrawn', [
                'name' => $notifiable->name,
                'articleTitle' => $this->article->getDualTitle(),
                'mainAuthor' => $this->article->mainAuthorDisplay,
                'withdrawDate' => now()->format('Y-m-d H:i'), // ✅ أضف هذا السطر
                'url' => URL::to("/admin/articles/{$this->article->id}/edit"),
                'actionText' => 'Article Withdrawn',
            ]);
    }

    public function toArray(mixed $notifiable): array
    {
        return [
            'article_id' => $this->article->id,
            'message' => "تم سحب المقالة: {$this->article->dual_title}",
            'message_en' => "Article withdrawn: {$this->article->dual_title}",
            'url' => "/adminpanel/articles/{$this->article->id}/edit",
        ];
    }
}

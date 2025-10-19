<?php

namespace App\Mail;

use App\Models\ReviewAssignment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable; // تأكد من استيراد Carbon

class ReviewerAssignmentMail extends Mailable
{
    use Queueable, SerializesModels;

    public $reviewAssignment;

    public function __construct(ReviewAssignment $reviewAssignment)
    {
        $this->reviewAssignment = $reviewAssignment;
    }

    public function build()
    {
        try {
            // التحقق الأساسي من وجود البيانات
            if (! $this->reviewAssignment) {
                throw new \Exception('Review assignment is null');
            }

            // تحميل العلاقات إذا لم تكن محملة
            if (! $this->reviewAssignment->relationLoaded('reviewer')) {
                $this->reviewAssignment->load('reviewer');
            }

            if (! $this->reviewAssignment->relationLoaded('article')) {
                $this->reviewAssignment->load('article.translations');
            }

            // التحقق من وجود المراجع
            if (! $this->reviewAssignment->reviewer) {
                throw new \Exception('Reviewer not found for assignment ID: '.$this->reviewAssignment->id);
            }

            // التحقق من أن reviewer هو نموذج User
            if (! $this->reviewAssignment->reviewer instanceof User) {
                throw new \Exception('Reviewer is not a User instance for assignment ID: '.$this->reviewAssignment->id);
            }

            // معالجة آمنة للبيانات
            $data = $this->prepareEmailData();

            return $this->from(config('mail.from.address'), config('mail.from.name'))
                ->subject('New Review Assignment - تعيين مراجعة جديدة')
                ->view('emails.reviewer-assignment', $data);

        } catch (Throwable $e) {
            Log::error('Error building email for assignment ID '.($this->reviewAssignment->id ?? 'unknown').': '.$e->getMessage());
            Log::error('Build error details - File: '.$e->getFile().' Line: '.$e->getLine());
            throw $e;
        }
    }

    protected function prepareEmailData(): array
    {
        $article = $this->reviewAssignment->article;
        $reviewer = $this->reviewAssignment->reviewer;

        // قيم افتراضية
        $articleTitleAr = 'لا يوجد عنوان';
        $articleTitleEn = 'No title';
        $articleAbstractAr = 'لا يوجد ملخص';
        $articleAbstractEn = 'No abstract';

        if ($article) {
            // تحميل الترجمات إذا لم تكن محملة
            if (! $article->relationLoaded('translations')) {
                $article->load('translations');
            }

            $arabicTranslation = $article->translations->where('locale', 'ar')->first();
            $englishTranslation = $article->translations->where('locale', 'en')->first();

            $articleTitleAr = $arabicTranslation?->title ?: ($article->title ?? 'لا يوجد عنوان');
            $articleTitleEn = $englishTranslation?->title ?: ($article->title ?? 'No title');
            $articleAbstractAr = $arabicTranslation?->abstract ?: ($article->abstract ?? 'لا يوجد ملخص');
            $articleAbstractEn = $englishTranslation?->abstract ?: ($article->abstract ?? 'No abstract');
        }

        // إصلاح معالجة التواريخ: استخدام Carbon::parse() لتحويل string إلى كائن Carbon
        $formattedAssignedAt = $this->reviewAssignment->assigned_at
            ? Carbon::parse($this->reviewAssignment->assigned_at)->format('Y-m-d')
            : 'غير محدد';

        $formattedDeadline = $this->reviewAssignment->deadline
            ? Carbon::parse($this->reviewAssignment->deadline)->format('Y-m-d')
            : 'غير محدد';

        return [
            'reviewAssignment' => $this->reviewAssignment,
            'article' => $article,
            'reviewer' => $reviewer,
            'articleTitleAr' => $articleTitleAr,
            'articleTitleEn' => $articleTitleEn,
            'articleAbstractAr' => $articleAbstractAr,
            'articleAbstractEn' => $articleAbstractEn,
            'formattedAssignedAt' => $formattedAssignedAt,
            'formattedDeadline' => $formattedDeadline,
        ];
    }
}

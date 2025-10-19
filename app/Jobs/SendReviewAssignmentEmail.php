<?php

namespace App\Jobs;

use App\Mail\ReviewerAssignmentMail;
use App\Models\ReviewAssignment;
use App\Models\User;
use App\Notifications\NewReviewAssignmentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendReviewAssignmentEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $reviewAssignmentId;

    public $tries = 2;

    public $timeout = 30;

    public function __construct($reviewAssignmentId)
    {
        $this->reviewAssignmentId = is_object($reviewAssignmentId)
            ? $reviewAssignmentId->id
            : $reviewAssignmentId;
    }

    public function handle(): void
    {
        try {
            Log::info('Starting email job for assignment ID: '.$this->reviewAssignmentId);

            // 1. البحث عن التعيين مع العلاقات المطلوبة
            $reviewAssignment = ReviewAssignment::with(['reviewer', 'article.translations'])
                ->find($this->reviewAssignmentId);

            if (! $reviewAssignment) {
                Log::error('Review assignment not found for ID: '.$this->reviewAssignmentId);

                return;
            }

            Log::info('Found assignment: '.$reviewAssignment->id.', Reviewer ID: '.$reviewAssignment->reviewer_id);

            // 2. التحقق من وجود المراجع
            if (! $reviewAssignment->reviewer) {
                Log::error('Reviewer not found for assignment: '.$reviewAssignment->id);

                return;
            }

            // 3. التحقق من أن reviewer هو نموذج User
            if (! $reviewAssignment->reviewer instanceof User) {
                Log::error('Reviewer is not a User instance: '.gettype($reviewAssignment->reviewer));

                return;
            }

            Log::info('Reviewer found: '.$reviewAssignment->reviewer->email);

            // 4. إرسال البريد الإلكتروني
            Mail::to($reviewAssignment->reviewer->email)
                ->send(new ReviewerAssignmentMail($reviewAssignment));

            Log::info('Email sent successfully to: '.$reviewAssignment->reviewer->email);

            // 5. إرسال الإشعار للمستخدم ⭐ جديد
            $reviewAssignment->reviewer->notify(new NewReviewAssignmentNotification($reviewAssignment));
            Log::info('Notification sent to user: '.$reviewAssignment->reviewer->name);

        } catch (Throwable $e) {
            Log::error('Email sending failed for assignment ID '.$this->reviewAssignmentId.': '.$e->getMessage());
            Log::error('Error details - File: '.$e->getFile().' Line: '.$e->getLine());

            $this->fail($e);
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error('SendReviewAssignmentEmail job failed completely for ID '.$this->reviewAssignmentId.': '.$exception->getMessage());
    }
}

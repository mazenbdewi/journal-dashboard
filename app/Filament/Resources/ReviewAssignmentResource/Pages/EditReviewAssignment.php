<?php

namespace App\Filament\Resources\ReviewAssignmentResource\Pages;

use App\Filament\Resources\ReviewAssignmentResource;
use App\Jobs\SendReviewAssignmentEmail;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Log;

class EditReviewAssignment extends EditRecord
{
    protected static string $resource = ReviewAssignmentResource::class;

    protected $oldReviewerId;

    protected function beforeSave(): void
    {
        // حفظ قيمة المراجع القديمة قبل التحديث
        $this->oldReviewerId = $this->record->reviewer_id;
    }

    protected function afterSave(): void
    {
        try {
            // إرسال البريد الإلكتروني إذا تغير المراجع
            if ($this->oldReviewerId != $this->record->reviewer_id) {
                // التصحيح: إرسال ID فقط وليس الكائن كاملاً
                SendReviewAssignmentEmail::dispatch($this->record->id) // تغيير هنا
                    ->delay(now()->addSeconds(3));

                Notification::make()
                    ->title('تم تحديث التعيين بنجاح')
                    ->body('سيتم إرسال بريد إلى المراجع الجديد')
                    ->success()
                    ->send();

                Log::info('Email job dispatched for updated assignment ID: '.$this->record->id);
            }
        } catch (\Exception $e) {
            Log::error('Error in EditReviewAssignment afterSave: '.$e->getMessage());
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

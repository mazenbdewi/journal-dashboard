<?php

namespace App\Filament\Resources\ReviewAssignmentResource\Pages;

use App\Filament\Resources\ReviewAssignmentResource;
use App\Jobs\SendReviewAssignmentEmail;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;

class CreateReviewAssignment extends CreateRecord
{
    protected static string $resource = ReviewAssignmentResource::class;

    protected function afterCreate(): void
    {
        try {
            // التحقق من أن التعيين تم إنشاؤه بنجاح
            if (! $this->record || ! $this->record->id) {
                Log::error('Failed to create review assignment record');

                return;
            }

            Log::info('Dispatching email job for assignment ID: '.$this->record->id);

            // إرسال البريد عبر Job
            SendReviewAssignmentEmail::dispatch($this->record->id)
                ->delay(now()->addSeconds(3));

            Notification::make()
                ->title('تم إنشاء التعيين بنجاح')
                ->body('سيتم إرسال البريد الإلكتروني إلى المراجع قريباً')
                ->success()
                ->send();

        } catch (\Exception $e) {
            Log::error('Error in afterCreate: '.$e->getMessage());

            Notification::make()
                ->title('تم الإنشاء ولكن حدث خطأ في إعداد البريد')
                ->body('سيتم إرسال البريد لاحقاً')
                ->warning()
                ->send();
        }
    }
}

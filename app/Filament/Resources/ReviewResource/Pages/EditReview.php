<?php

namespace App\Filament\Resources\ReviewResource\Pages;

use App\Filament\Resources\ReviewResource;
use App\Models\Review;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EditReview extends EditRecord
{
    protected static string $resource = ReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $review = Review::with('article.translations')->find($data['id']);

        return array_merge($data, [
            'article_id' => $review->article_id,
        ]);
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return __('review.updated_successfully');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    // دالة للتعامل مع تحديث القرار
    public function onDecisionUpdated($decision): void
    {
        Log::info('تم تحديث القرار في الواجهة', [
            'review_id' => $this->record->id,
            'decision' => $decision,
        ]);
    }

    // دالة للتعامل مع تحديث الحالة
    public function onStatusUpdated($status): void
    {
        Log::info('تم تحديث الحالة في الواجهة', [
            'review_id' => $this->record->id,
            'status' => $status,
        ]);
    }

    protected function getListeners(): array
    {
        return [
            ...parent::getListeners(),
            'decisionUpdated' => 'onDecisionUpdated',
            'statusUpdated' => 'onStatusUpdated',
        ];
    }

    /**
     * حظر الدخول إن كانت المقالة مسحوبة (revoke) والمستخدم مراجع فقط
     */
    public function mount($record): void
    {
        parent::mount($record);

        if (
            Auth::user()->hasRole('reviewer') &&
            $this->record->article?->status === 'revoke'
        ) {
            abort(403, 'لا يمكنك تعديل مراجعة لمقالة تم سحبها من قبل الباحث.');
        }
    }

    /**
     * تجهيز البيانات قبل الحفظ
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // حماية إضافية من الحفظ إذا كانت المقالة مسحوبة
        if (
            Auth::user()->hasRole('reviewer') &&
            $this->record->article?->status === 'revoke'
        ) {
            abort(403, 'لا يمكنك حفظ مراجعة لمقالة تم سحبها.');
        }

        if ($this->record) {
            Log::info('بيانات قبل الحفظ', [
                'review_id' => $this->record->id,
                'current_decision' => $this->record->decision,
                'new_decision' => $data['decision'] ?? null,
                'decision_changed' => $this->record->decision !== ($data['decision'] ?? null),
            ]);
        }

        return $data;
    }

    /**
     * بعد الحفظ
     */
    protected function afterSave(): void
    {
        Log::info('تم حفظ المراجعة', [
            'review_id' => $this->record->id,
            'decision' => $this->record->decision,
            'status' => $this->record->status,
        ]);
    }
}

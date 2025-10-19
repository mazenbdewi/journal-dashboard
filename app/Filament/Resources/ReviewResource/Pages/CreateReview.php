<?php

namespace App\Filament\Resources\ReviewResource\Pages;

use App\Filament\Resources\ReviewResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;

class CreateReview extends CreateRecord
{
    protected static string $resource = ReviewResource::class;

    /**
     * بعد إنشاء السجل
     */
    protected function afterCreate(): void
    {
        Log::info('تم إنشاء مراجعة جديدة', [
            'review_id' => $this->record->id,
            'decision' => $this->record->decision,
            'article_id' => $this->record->article_id,
        ]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

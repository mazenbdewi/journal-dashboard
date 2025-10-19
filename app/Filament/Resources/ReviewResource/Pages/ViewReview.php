<?php

use App\Filament\Resources\ReviewResource;
use App\Filament\Resources\ReviewResource\RelationManagers\RevisionsRelationManager;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewReview extends ViewRecord
{
    protected static string $resource = ReviewResource::class;

    // أضف هذه الدالة لعرض العلاقة
    protected function getRelations(): array
    {
        return [
            RevisionsRelationManager::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}

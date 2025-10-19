<?php

namespace App\Filament\Resources\ReviewAssignmentResource\Pages;

use App\Filament\Resources\ReviewAssignmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReviewAssignments extends ListRecords
{
    protected static string $resource = ReviewAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];

    }
}

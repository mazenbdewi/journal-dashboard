<?php

namespace App\Filament\Resources\HomeStatResource\Pages;

use App\Filament\Resources\HomeStatResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHomeStats extends ListRecords
{
    protected static string $resource = HomeStatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

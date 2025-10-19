<?php

namespace App\Filament\Resources\HomeAboutSectionResource\Pages;

use App\Filament\Resources\HomeAboutSectionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHomeAboutSections extends ListRecords
{
    protected static string $resource = HomeAboutSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

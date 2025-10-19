<?php

namespace App\Filament\Resources\HomeHeroSectionResource\Pages;

use App\Filament\Resources\HomeHeroSectionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHomeHeroSections extends ListRecords
{
    protected static string $resource = HomeHeroSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\HomeHeroSectionResource\Pages;

use App\Filament\Resources\HomeHeroSectionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewHomeHeroSection extends ViewRecord
{
    protected static string $resource = HomeHeroSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}

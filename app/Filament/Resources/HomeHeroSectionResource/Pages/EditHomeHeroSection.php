<?php

namespace App\Filament\Resources\HomeHeroSectionResource\Pages;

use App\Filament\Resources\HomeHeroSectionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHomeHeroSection extends EditRecord
{
    protected static string $resource = HomeHeroSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}

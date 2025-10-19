<?php

namespace App\Filament\Resources\HomePartnerResource\Pages;

use App\Filament\Resources\HomePartnerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHomePartners extends ListRecords
{
    protected static string $resource = HomePartnerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

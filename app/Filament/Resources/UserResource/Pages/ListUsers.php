<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        // يظهر زر الإضافة فقط للمشرف الأعلى
        if (auth()->user()->hasRole('super_admin')) {
            return [
                Actions\CreateAction::make(),
            ];
        }

        return [];
    }
}

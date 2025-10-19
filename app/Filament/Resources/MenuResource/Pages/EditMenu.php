<?php

namespace App\Filament\Resources\MenuResource\Pages;

use App\Filament\Resources\MenuResource;
use Filament\Actions;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;

class EditMenu extends EditRecord
{
    protected static string $view = 'filament-menu-builder::edit-record';

    public static function getResource(): string
    {
        return MenuResource::class;
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Section::make()->schema($form->getComponents()),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

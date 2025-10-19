<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HomePartnerResource\Pages;
use App\Models\HomePartner;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class HomePartnerResource extends Resource
{
    protected static ?string $model = HomePartner::class;

    protected static ?string $navigationIcon = 'heroicon-o-link';

    public static function getNavigationLabel(): string
    {
        return __('home_partner.navigation.label');
    }

    public static function getLabel(): string
    {
        return __('home_partner.navigation.singular');
    }

    public static function getPluralLabel(): string
    {
        return __('home_partner.navigation.plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('home_partner.navigation.group');
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        return ! ($user->hasRole('researcher') || $user->hasRole('reviewer'));
    }

    protected static ?int $navigationSort = 11;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\FileUpload::make('image')
                ->label(__('home_partner.form.image'))
                ->image()
                ->directory('partners')
                ->nullable(),

            Forms\Components\TextInput::make('link')
                ->label(__('home_partner.form.link'))
                ->url()
                ->nullable(),

            Forms\Components\TextInput::make('order')
                ->label(__('home_partner.form.order'))
                ->numeric()
                ->default(0),

            Forms\Components\Toggle::make('active')
                ->label(__('home_partner.form.active'))
                ->default(true),

            Forms\Components\Repeater::make('translations')
                ->label(__('home_partner.form.translations'))
                ->relationship()
                ->schema([
                    Forms\Components\Select::make('locale')
                        ->label(__('home_partner.form.language'))
                        ->options([
                            'ar' => '🇸🇦 العربية',
                            'en' => '🇺🇸 English',
                        ])
                        ->required(),

                    Forms\Components\TextInput::make('title')
                        ->label(__('home_partner.form.title'))
                        ->nullable(),
                ])
                ->columnSpanFull()
                ->createItemButtonLabel(__('home_partner.form.add_translation')),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\ImageColumn::make('image')
                ->label(__('home_partner.table.image')),

            Tables\Columns\TextColumn::make('link')
                ->label(__('home_partner.table.link'))
                ->limit(30),

            Tables\Columns\TextColumn::make('translations.title')
                ->label(__('home_partner.table.title'))
                ->limit(30),

            Tables\Columns\BooleanColumn::make('active')
                ->label(__('home_partner.table.active')),

            Tables\Columns\TextColumn::make('order')
                ->label(__('home_partner.table.order')),

            Tables\Columns\TextColumn::make('created_at')
                ->since()
                ->label(__('home_partner.table.created_at')),
        ])
            ->defaultSort('order')
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHomePartners::route('/'),
            'create' => Pages\CreateHomePartner::route('/create'),
            'edit' => Pages\EditHomePartner::route('/{record}/edit'),
        ];
    }
}

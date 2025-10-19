<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HomeHeroSectionResource\Pages;
use App\Models\HomeHeroSection;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class HomeHeroSectionResource extends Resource
{
    protected static ?string $model = HomeHeroSection::class;

    protected static ?string $navigationIcon = 'heroicon-o-home';

    public static function getNavigationLabel(): string
    {
        return __('home_hero.navigation.label');
    }

    public static function getLabel(): string
    {
        return __('home_hero.navigation.singular');
    }

    public static function getPluralLabel(): string
    {
        return __('home_hero.navigation.plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('home_hero.navigation.group');
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        return ! ($user->hasRole('researcher') || $user->hasRole('reviewer'));
    }

    protected static ?int $navigationSort = 8;

    public static function form(Form $form): Form
    {
        return $form->schema([

            Section::make(__('home_hero.form.section_media'))
                ->description(__('home_hero.form.section_media_description'))
                ->schema([
                    Forms\Components\FileUpload::make('image')
                        ->image()
                        ->label(__('home_hero.form.image'))
                        ->directory('hero')
                        ->columnSpanFull(),

                    Forms\Components\Toggle::make('active')
                        ->label(__('home_hero.form.active'))
                        ->default(true),
                ])
                ->columns(2),

            Section::make(__('home_hero.form.section_translations'))
                ->description(__('home_hero.form.section_translations_description'))
                ->schema([
                    Forms\Components\Repeater::make('translations')
                        ->label(__('home_hero.form.translations'))
                        ->relationship()
                        ->schema([
                            Forms\Components\Select::make('locale')
                                ->label(__('home_hero.form.language'))
                                ->options([
                                    'ar' => '🇸🇦 العربية',
                                    'en' => '🇺🇸 English',
                                ])
                                ->required(),

                            Forms\Components\TextInput::make('title')
                                ->label(__('home_hero.form.title'))
                                ->required(),

                            Forms\Components\Textarea::make('description')
                                ->label(__('home_hero.form.description')),
                        ])
                        ->columnSpanFull()
                        ->createItemButtonLabel(__('home_hero.form.add_translation')),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label(__('home_hero.table.image'))
                    ->width(50),

                Tables\Columns\TextColumn::make('translations.title')
                    ->label(__('home_hero.table.title'))
                    ->limit(30),

                Tables\Columns\BooleanColumn::make('active')
                    ->label(__('home_hero.table.active')),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('home_hero.table.created_at'))
                    ->since(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListHomeHeroSections::route('/'),
            'create' => Pages\CreateHomeHeroSection::route('/create'),
            'view' => Pages\ViewHomeHeroSection::route('/{record}'),
            'edit' => Pages\EditHomeHeroSection::route('/{record}/edit'),
        ];
    }
}

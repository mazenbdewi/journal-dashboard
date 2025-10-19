<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HomeAboutSectionResource\Pages;
use App\Models\HomeAboutSection;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class HomeAboutSectionResource extends Resource
{
    protected static ?string $model = HomeAboutSection::class;

    protected static ?string $navigationIcon = 'heroicon-o-information-circle';

    public static function getNavigationLabel(): string
    {
        return __('home_about.navigation.label');
    }

    public static function getLabel(): string
    {
        return __('home_about.navigation.singular');
    }

    public static function getPluralLabel(): string
    {
        return __('home_about.navigation.plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('home_about.navigation.group');
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        return ! ($user->hasRole('researcher') || $user->hasRole('reviewer'));
    }

    protected static ?int $navigationSort = 9;

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Toggle::make('active')
                ->label(__('home_about.form.active'))
                ->default(true),

            Forms\Components\Repeater::make('translations')
                ->label(__('home_about.form.translations'))
                ->relationship()
                ->schema([
                    Forms\Components\Select::make('locale')
                        ->label(__('home_about.form.language'))
                        ->options([
                            'ar' => '🇸🇦 العربية',
                            'en' => '🇺🇸 English',
                        ])
                        ->required(),

                    Forms\Components\TextInput::make('title')
                        ->label(__('home_about.form.title'))

                        ->required(),

                    Forms\Components\Textarea::make('description')
                        ->label(__('home_about.form.description'))
                        ->maxLength(238) // الحد الأقصى لعدد الأحرف
                        ->helperText('الحد الأقصى 238 حرفًا')
                        ->rows(3),

                    Forms\Components\Textarea::make('vision')
                        ->label(__('home_about.form.vision'))
                        ->maxLength(238) // الحد الأقصى لعدد الأحرف
                        ->helperText('الحد الأقصى 238 حرفًا')
                        ->rows(2),

                    Forms\Components\Textarea::make('mission')
                        ->label(__('home_about.form.mission'))
                        ->maxLength(238) // الحد الأقصى لعدد الأحرف
                        ->helperText('الحد الأقصى 238 حرفًا')
                        ->rows(2),

                    Forms\Components\Textarea::make('goals')
                        ->label(__('home_about.form.goals'))
                        ->maxLength(238) // الحد الأقصى لعدد الأحرف
                        ->helperText('الحد الأقصى 238 حرفًا')
                        ->rows(3),
                ])
                ->columnSpanFull()
                ->createItemButtonLabel(__('home_about.form.add_translation')),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('translations.title')
                    ->label(__('home_about.table.title'))
                    ->limit(30),

                Tables\Columns\BooleanColumn::make('active')
                    ->label(__('home_about.table.active')),

                Tables\Columns\TextColumn::make('created_at')
                    ->since()
                    ->label(__('home_about.table.created_at')),
            ])
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
            'index' => Pages\ListHomeAboutSections::route('/'),
            'create' => Pages\CreateHomeAboutSection::route('/create'),
            'edit' => Pages\EditHomeAboutSection::route('/{record}/edit'),
        ];
    }
}

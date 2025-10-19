<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HomeStatResource\Pages;
use App\Models\HomeStat;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class HomeStatResource extends Resource
{
    protected static ?string $model = HomeStat::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    public static function getNavigationLabel(): string
    {
        return __('home_stat.navigation.label');
    }

    public static function getLabel(): string
    {
        return __('home_stat.navigation.singular');
    }

    public static function getPluralLabel(): string
    {
        return __('home_stat.navigation.plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('home_stat.navigation.group');
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        return ! ($user->hasRole('researcher') || $user->hasRole('reviewer'));
    }

    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('number')
                ->label(__('home_stat.form.number'))
                ->numeric()
                ->required(),

            Forms\Components\TextInput::make('icon')
                ->label(__('home_stat.form.icon'))
                ->helperText(__('home_stat.form.icon_helper'))
                ->nullable(),

            Forms\Components\TextInput::make('order')
                ->label(__('home_stat.form.order'))
                ->numeric()
                ->default(0),

            Forms\Components\Toggle::make('active')
                ->label(__('home_stat.form.active'))
                ->default(true),

            Forms\Components\Repeater::make('translations')
                ->label(__('home_stat.form.translations'))
                ->relationship()
                ->schema([
                    Forms\Components\Select::make('locale')
                        ->label(__('home_stat.form.language'))
                        ->options([
                            'ar' => '🇸🇦 العربية',
                            'en' => '🇺🇸 English',
                        ])
                        ->required(),

                    Forms\Components\TextInput::make('label')
                        ->label(__('home_stat.form.label'))
                        ->required(),
                ])
                ->columnSpanFull()
                ->createItemButtonLabel(__('home_stat.form.add_translation')),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('translations.label')
                    ->label(__('home_stat.table.label'))
                    ->limit(30),

                Tables\Columns\TextColumn::make('number')
                    ->label(__('home_stat.table.number')),

                Tables\Columns\TextColumn::make('icon')
                    ->label(__('home_stat.table.icon'))
                    ->html()
                    ->formatStateUsing(fn (string $state) => "<i class=\"{$state} fs-4 text-muted\"></i>"),

                Tables\Columns\BooleanColumn::make('active')
                    ->label(__('home_stat.table.active')),

                Tables\Columns\TextColumn::make('order')
                    ->label(__('home_stat.table.order'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->since()
                    ->label(__('home_stat.table.created_at')),
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
            'index' => Pages\ListHomeStats::route('/'),
            'create' => Pages\CreateHomeStat::route('/create'),
            'edit' => Pages\EditHomeStat::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeamMemberResource\Pages;
use App\Models\TeamMember;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TeamMemberResource extends Resource
{
    protected static ?string $model = TeamMember::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function getNavigationLabel(): string
    {
        return __('team.navigation.label');
    }

    public static function getLabel(): string
    {
        return __('team.navigation.singular');
    }

    public static function getPluralLabel(): string
    {
        return __('team.navigation.plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('team.navigation.group');
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        return ! ($user->hasRole('researcher') || $user->hasRole('reviewer'));
    }

    protected static ?int $navigationSort = 12;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\FileUpload::make('image')
                ->label(__('team.form.image'))
                ->image()
                ->directory('team')
                ->nullable(),

            Forms\Components\TextInput::make('twitter')
                ->label(__('team.form.twitter'))
                ->url()
                ->nullable(),

            Forms\Components\TextInput::make('facebook')
                ->label(__('team.form.facebook'))
                ->url()
                ->nullable(),

            Forms\Components\TextInput::make('instagram')
                ->label(__('team.form.instagram'))
                ->url()
                ->nullable(),

            Forms\Components\TextInput::make('linkedin')
                ->label(__('team.form.linkedin'))
                ->url()
                ->nullable(),

            Forms\Components\TextInput::make('order')
                ->label(__('team.form.order'))
                ->numeric()
                ->default(0),

            Forms\Components\Toggle::make('active')
                ->label(__('team.form.active'))
                ->default(true),

            Forms\Components\Repeater::make('translations')
                ->label(__('team.form.translations'))
                ->relationship()
                ->schema([
                    Forms\Components\Select::make('locale')
                        ->label(__('team.form.language'))
                        ->options([
                            'ar' => '🇸🇦 العربية',
                            'en' => '🇺🇸 English',
                        ])
                        ->required(),

                    Forms\Components\TextInput::make('name')
                        ->label(__('team.form.name'))
                        ->required(),

                    Forms\Components\TextInput::make('position')
                        ->label(__('team.form.position'))
                        ->nullable(),

                    Forms\Components\Textarea::make('bio')
                        ->label(__('team.form.bio'))
                        ->rows(3)
                        ->nullable(),
                ])
                ->columnSpanFull()
                ->createItemButtonLabel(__('team.form.add_translation')),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label(__('team.table.image')),

                Tables\Columns\TextColumn::make('translations.name')
                    ->label(__('team.table.name'))
                    ->limit(30),

                Tables\Columns\TextColumn::make('translations.position')
                    ->label(__('team.table.position'))
                    ->limit(30),

                Tables\Columns\BooleanColumn::make('active')
                    ->label(__('team.table.active')),

                Tables\Columns\TextColumn::make('order')
                    ->label(__('team.table.order')),

                Tables\Columns\TextColumn::make('created_at')
                    ->since()
                    ->label(__('team.table.created_at')),
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
            'index' => Pages\ListTeamMembers::route('/'),
            'create' => Pages\CreateTeamMember::route('/create'),
            'edit' => Pages\EditTeamMember::route('/{record}/edit'),
        ];
    }
}

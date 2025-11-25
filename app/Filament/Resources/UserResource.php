<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Monarobase\CountryList\CountryListFacade as Countries;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 13;

    public static function getNavigationLabel(): string
    {
        return Auth::user()->hasRole('super_admin') ? __('user.navigation.label') : __('user.navigation.label_user');
    }

    public static function getLabel(): string
    {
        return Auth::user()->hasRole('super_admin') ? __('user.navigation.singular') : __('user.navigation.label_user');
    }

    public static function getPluralLabel(): string
    {
        return Auth::user()->hasRole('super_admin') ? __('user.navigation.plural') : __('user.navigation.label_user');
    }

    public static function getNavigationGroup(): ?string
    {
        return Auth::user()->hasRole('super_admin') ? __('user.navigation.group') : __('user.navigation.label_user');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        if (! Auth::user()->hasRole('super_admin')) {
            return $query->where('id', Auth::id());
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        $isSuperAdmin = Auth::user()->hasRole('super_admin');

        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label(__('user.form.name'))
                            ->required()
                            ->maxLength(255)
                            ->disabled(! $isSuperAdmin),
                        Forms\Components\TextInput::make('name_en')
                            ->label(__('user.form.name_en'))
                            ->required()
                            ->maxLength(255)
                            ->disabled(! $isSuperAdmin),

                        Forms\Components\TextInput::make('email')
                            ->label(__('user.form.email'))
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->disabled(! $isSuperAdmin),
                        Forms\Components\DateTimePicker::make('email_verified_at')
                            ->label(__('تأكيد'))

                            ->required()
                            ->maxLength(255)
                            ->disabled(! $isSuperAdmin),

                        Forms\Components\Section::make(__('user.navigation.label_user'))
                            ->schema([
                                Forms\Components\Select::make('user_type')
                                    ->label(__('user.form.user_type'))
                                    ->required()
                                    ->options(__('user.form.user_type_options'))
                                    ->searchable(),

                                Forms\Components\FileUpload::make('student_form')
                                    ->label(__('user.form.student_form'))
                                    ->directory('student_forms')
                                    ->acceptedFileTypes(['application/pdf'])
                                    ->preserveFilenames()
                                    ->downloadable()
                                    ->nullable(),

                                Forms\Components\TextInput::make('phone')
                                    ->label(__('user.form.phone'))
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\Select::make('country')
                                    ->label(__('user.form.country'))
                                    ->options(Countries::getList(app()->getLocale()))
                                    ->searchable()
                                    ->nullable(),

                                Forms\Components\Select::make('nationality')
                                    ->label(__('user.form.nationality'))
                                    ->options(Countries::getList(app()->getLocale()))
                                    ->searchable()
                                    ->nullable(),

                                Forms\Components\Textarea::make('address')
                                    ->label(__('user.form.address'))
                                    ->required()
                                    ->rows(2),

                                Forms\Components\TextInput::make('orcid')
                                    ->label('ORCID')
                                    ->maxLength(255)
                                    ->nullable(),

                                Forms\Components\Textarea::make('affiliation')
                                    ->label(__('user.form.affiliation'))
                                    ->rows(2)
                                    ->nullable(),

                                Forms\Components\Textarea::make('bio')
                                    ->label(__('user.form.bio'))
                                    ->rows(3)
                                    ->nullable(),
                            ])
                            ->columns(3),

                        Forms\Components\Select::make('roles')
                            ->relationship('roles', 'name')
                            ->label(__('user.form.roles'))
                            ->required()
                            ->preload()
                            ->searchable()
                            ->disabled(! $isSuperAdmin),
                    ])->columns(3),

                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('password')
                            ->label(__('user.form.password'))
                            ->password()
                            ->required(fn (string $context) => $context === 'create')
                            ->dehydrated(fn ($state) => filled($state))
                            ->maxLength(255)
                            ->autocomplete('new-password')
                            ->confirmed(),

                        Forms\Components\TextInput::make('password_confirmation')
                            ->label(__('user.form.confirm_password'))
                            ->password()
                            ->maxLength(255)
                            ->dehydrated(false),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label(__('user.table.name'))->searchable(),
                Tables\Columns\TextColumn::make('name_en')->label(__('user.table.name_en'))->searchable(),
                Tables\Columns\TextColumn::make('email')->label(__('user.table.email'))->searchable(),
                Tables\Columns\TextColumn::make('country')
                    ->label(__('user.table.country'))
                    ->formatStateUsing(fn ($state) => Countries::getOne($state, app()->getLocale()))
                    ->searchable(),
                Tables\Columns\TextColumn::make('nationality')
                    ->label(__('user.table.nationality'))
                    ->formatStateUsing(fn ($state) => Countries::getOne($state, app()->getLocale()))
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')->label(__('user.table.created_at'))->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')->label(__('user.table.updated_at'))->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->visible(fn ($record) => Auth::user()->hasRole('super_admin') || Auth::id() === $record->id),
                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) => Auth::user()->hasRole('super_admin') || Auth::id() === $record->id),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => Auth::user()->hasRole('super_admin')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => Auth::user()->hasRole('super_admin')),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    // في UserResource
    public static function canDelete(Model $record): bool
    {
        // التحقق من وجود مقالات مرتبطة
        if ($record->articles()->exists() || \App\Models\Article::where('created_by', $record->id)->exists()) {
            return false;
        }

        // التحقق من أن المستخدم ليس المدير الوحيد
        if ($record->hasRole('super_admin')) {
            $superAdminsCount = \App\Models\User::whereHas('roles', function ($query) {
                $query->where('name', 'super_admin');
            })->count();

            if ($superAdminsCount <= 1) {
                return false;
            }
        }

        return true;
    }
}

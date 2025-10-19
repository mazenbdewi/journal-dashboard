<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MenuResource\Pages;
use App\Models\Menu;
use Closure;
use Datlechin\FilamentMenuBuilder\Resources\MenuResource as BaseMenuResource;
use Filament\Forms\Components;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class MenuResource extends BaseMenuResource
{
    protected static ?string $model = Menu::class;

    // protected static ?string $slug = 'menu-customs';

    // protected static ?string $navigationIcon = 'heroicon-o-bars-3';

    // public static function getNavigationGroup(): ?string
    // {
    //     return __('menu.group');
    // }

    // protected static ?int $navigationSort = 7;

    // public static function getNavigationLabel(): string
    // {
    //     return __('menu.navigation_label');
    // }

    public static function getModelLabel(): string
    {
        return __('menu.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('menu.plural_label');
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        return ! ($user->hasRole('researcher') || $user->hasRole('reviewer'));
    }

    public static function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                Components\Grid::make(4)
                    ->schema([
                        Components\TextInput::make('name')
                            ->label(__('menu.name'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->columnSpan(2),

                        Select::make('journal_id')
                            ->label(__('menu.journal'))
                            ->options(
                                \App\Models\Journal::with('translations')
                                    ->get()
                                    ->mapWithKeys(fn ($journal) => [
                                        $journal->id => $journal->currentTranslation?->title ?? $journal->name,
                                    ])
                            )
                            ->searchable()
                            ->required()
                            ->rule(function ($record, $state, $get) {
                                return function (string $attribute, $value, Closure $fail) use ($record, $get) {
                                    $language = $get('language');
                                    if (! $language) {
                                        return;
                                    }

                                    $query = DB::table('menus')
                                        ->where('language', $language)
                                        ->when($record?->id, fn ($q) => $q->where('id', '!=', $record->id))
                                        ->where('journal_id', $value);

                                    if ($query->exists()) {
                                        $fail(__('menu.duplicate_error_journal', ['language' => $language]));
                                    }
                                };
                            }),

                        Select::make('language')
                            ->label(__('menu.language'))
                            ->options([
                                'ar' => __('menu.language_ar'),
                                'en' => __('menu.language_en'),
                            ])
                            ->native(false)
                            ->required(),

                        Components\ToggleButtons::make('is_visible')
                            ->grouped()
                            ->options([
                                true => __('menu.visible'),
                                false => __('menu.hidden'),
                            ])
                            ->colors([
                                true => 'primary',
                                false => 'danger',
                            ])
                            ->required()
                            ->label(__('menu.is_visible'))
                            ->default(true),

                    ]),

                // إضافة حقول القائمة إذا كانت موجودة في الحزمة
                Components\Group::make()
                    ->schema([

                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(fn () => Menu::query()->with('journal.translations'))
            ->modifyQueryUsing(fn ($query) => $query->withCount('menuItems'))
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('menu.name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('journal_title')
                    ->label(__('menu.journal'))
                    ->getStateUsing(function ($record) {
                        $locale = app()->getLocale();

                        $translation = $record->journal?->translations
                            ->firstWhere('locale', $locale)
                            ?? $record->journal?->translations->first();

                        return $translation?->title ?? __('menu.general');
                    })
                    ->sortable()
                    ->searchable(query: function ($query, $search) {
                        $locale = app()->getLocale();

                        $query->whereHas('journal.translations', function ($q) use ($locale, $search) {
                            $q->where('locale', $locale)
                                ->where('title', 'like', "%{$search}%");
                        });
                    }),

                Tables\Columns\TextColumn::make('language')
                    ->label(__('menu.language'))
                    ->formatStateUsing(fn ($state) => $state === 'ar' ? __('menu.language_ar') : __('menu.language_en'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('menu_items_count')
                    ->label(__('menu.items'))
                    ->icon('heroicon-o-link')
                    ->numeric()
                    ->default(0)
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_visible')
                    ->label(__('menu.is_visible'))
                    ->boolean()
                    ->sortable(),
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
        return [
            // إضافة علاقة عناصر القائمة إذا لزم الأمر
        ];
    }

    // public static function getPages(): array
    // {
    //     return [
    //         'index' => Pages\ListMenus::route('/'),
    //         'edit' => Pages\EditMenu::route('/{record}/edit'),
    //     ];
    // }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMenus::route('/'),
            'edit' => Pages\EditMenu::route('/{record}/edit'),
        ];
    }
}

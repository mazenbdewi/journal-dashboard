<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JournalResource\Pages;
use App\Models\Journal;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class JournalResource extends Resource
{
    protected static ?string $model = Journal::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationLabel = null;

    protected static ?string $pluralModelLabel = null;

    public static function getNavigationLabel(): string
    {
        return __('journal.navigation_label');
    }

    public static function getLabel(): string
    {
        return __('journal.model_label');
    }

    public static function getPluralLabel(): string
    {
        return __('journal.plural_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('journal.navigation_group');
    }

    protected static ?int $navigationSort = 01;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make(__('journal.basic_info'))
                ->schema([
                    TextInput::make('code')
                        ->label(__('journal.code'))
                        ->required()
                        ->maxLength(50)
                        ->unique(ignoreRecord: true)
                        ->disabled(fn ($record) => $record?->code === 'general')
                        ->columnSpan(1),

                    TextInput::make('issn')
                        ->label(__('journal.issn'))
                        ->maxLength(20)
                        ->disabled(fn ($record) => $record?->code === 'general')
                        ->columnSpan(1),

                    TextInput::make('e_issn')
                        ->label(__('journal.e_issn'))
                        ->maxLength(20)
                        ->disabled(fn ($record) => $record?->code === 'general')
                        ->columnSpan(1),

                    TextInput::make('name')
                        ->label(__('journal.name'))
                        ->required()
                        ->maxLength(255)
                        ->reactive()
                        ->debounce(500)
                        ->disabled(fn ($record) => $record?->code === 'general')
                        ->columnSpan(2),
                ])
                ->columns(4),

            Section::make(__('journal.translations'))
                ->schema([
                    Repeater::make('translations')
                        ->relationship()
                        ->label('')
                        ->schema([
                            Select::make('locale')
                                ->label(__('journal.language'))
                                ->options([
                                    'ar' => 'العربية',
                                    'en' => 'English',
                                ])
                                ->required()
                                ->native(false)
                                ->columnSpan(1),

                            TextInput::make('title')
                                ->label(__('journal.title'))
                                ->required()
                                ->maxLength(200)
                                ->columnSpan(2)
                                ->afterStateUpdated(function ($state, callable $set) {
                                    if (! empty($state)) {
                                        $slug = mb_strtolower($state);
                                        $slug = preg_replace('/[^\p{L}\p{N}]+/u', '-', $slug);
                                        $slug = trim($slug, '-');
                                        $set('slug', $slug);
                                    }
                                }),
                            Hidden::make('slug')
                                ->label(__('journal.slug'))
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->columnSpan(2),

                            Textarea::make('description')
                                ->label(__('journal.description'))
                                ->rows(3)
                                ->columnSpan(3),

                            FileUpload::make('image')
                                ->label(__('journal.image'))
                                ->disk('journal')
                                ->directory('')
                                ->image()
                                ->imageEditor()
                                ->imageResizeTargetWidth(800)
                                ->imageResizeTargetHeight(600)
                                ->imageResizeMode('cover')
                                ->maxSize(2048)
                                ->columnSpan(3)
                                ->helperText(__('journal.image_helper'))
                                ->storeFileNamesIn('image_names')
                                ->preserveFilenames(),
                        ])
                        ->columns(4)
                        ->collapsible()
                        ->defaultItems(2),
                ])
                ->columnSpanFull(),

            Section::make(__('journal.creator_section'))
                ->schema([
                    Hidden::make('created_by')
                        ->default(auth()->id()),

                    Placeholder::make('creator_name')
                        ->label(__('journal.creator'))
                        ->content(auth()->user()->name),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\ImageColumn::make('currentTranslation.image')
                ->label('')
                ->disk('journal')
                ->height(80)
                ->width(60)
                ->defaultImageUrl(asset('images/default-journal.png'))
                ->grow(false),

            Tables\Columns\TextColumn::make('code')
                ->label(__('journal.code'))
                ->searchable(),

            Tables\Columns\TextColumn::make('issn')
                ->label(__('journal.issn')),

            Tables\Columns\TextColumn::make('name')
                ->label(__('journal.name'))
                ->limit(30),
        ])
            ->filters([
                Tables\Filters\Filter::make('has_issn')
                    ->label(__('journal.has_issn'))
                    ->toggle()
                    ->query(fn (Builder $query) => $query->whereNotNull('issn')),
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
            'index' => Pages\ListJournals::route('/'),
            'create' => Pages\CreateJournal::route('/create'),
            'view' => Pages\ViewJournal::route('/{record}'),
            'edit' => Pages\EditJournal::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        return ! ($user->hasRole('researcher') || $user->hasRole('reviewer'));
    }
}

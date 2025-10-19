<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IssueResource\Pages;
use App\Models\Issue;
use App\Models\Journal;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class IssueResource extends Resource
{
    protected static ?string $model = Issue::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document';

    protected static ?int $navigationSort = 02;

    public static function getNavigationLabel(): string
    {
        return __('issue.navigation_label');
    }

    public static function getLabel(): string
    {
        return __('issue.model_label');
    }

    public static function getPluralLabel(): string
    {
        return __('issue.plural_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('issue.navigation_group');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('issue.basic_info'))
                ->schema([
                    Forms\Components\Select::make('journal_id')
                        ->label(__('issue.journal'))
                        ->relationship('journal', 'name')
                        ->live()
                        ->searchable()
                        ->required()
                        ->preload()
                        ->reactive()
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('volume')
                        ->label(__('issue.volume'))
                        ->required()
                        ->reactive()
                        ->debounce(500)
                        ->afterStateUpdated(function ($state, $set, $get) {
                            self::generateSlug($get, $set);
                        })
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('number')
                        ->label(__('issue.number'))
                        ->required()
                        ->reactive()
                        ->debounce(500)
                        ->afterStateUpdated(function ($state, $set, $get) {
                            self::generateSlug($get, $set);
                        })
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('year')
                        ->label(__('issue.year'))
                        ->required()
                        ->reactive()
                        ->debounce(500)
                        ->afterStateUpdated(function ($state, $set, $get) {
                            self::generateSlug($get, $set);
                        })
                        ->columnSpan(1),

                    Forms\Components\DatePicker::make('published_at')
                        ->label(__('issue.published_at'))
                        ->columnSpan(2),

                    Forms\Components\Toggle::make('is_published')
                        ->label(__('issue.is_published'))
                        ->default(true)
                        ->columnSpan(2),
                ])->columns(3),

            Forms\Components\Section::make(__('issue.translations'))
                ->schema([
                    Forms\Components\Repeater::make('translations')
                        ->label('')
                        ->relationship()
                        ->schema([
                            Forms\Components\Select::make('locale')
                                ->label(__('issue.language'))
                                ->options([
                                    'ar' => 'العربية',
                                    'en' => 'English',
                                ])
                                ->required()
                                ->native(false)
                                ->columnSpan(1),

                            Forms\Components\TextInput::make('title')
                                ->label(__('issue.title'))
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
                            Forms\Components\Hidden::make('slug')
                                ->label(__('issue.slug'))
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->columnSpan(2),

                            Forms\Components\Textarea::make('description')
                                ->label(__('issue.description'))
                                ->rows(3)
                                ->columnSpan(3),

                            FileUpload::make('image')
                                ->label(__('issue.image'))
                                ->disk('issue')
                                ->directory('')
                                ->image()
                                ->imageEditor()
                                ->imageResizeTargetWidth(1200)
                                ->imageResizeTargetHeight(630)
                                ->imageResizeMode('cover')
                                ->maxSize(2048)
                                ->columnSpan(3)
                                ->helperText(__('issue.image_helper'))
                                ->storeFileNamesIn('image_names')
                                ->preserveFilenames(),
                        ])
                        ->columns(6)
                        ->columnSpanFull()
                        ->defaultItems(1)
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                        ->addActionLabel(__('issue.add_translation')),
                ]),

            Forms\Components\Section::make(__('issue.creator_section'))
                ->schema([
                    Forms\Components\Hidden::make('created_by')
                        ->default(auth()->id()),

                    Forms\Components\Placeholder::make('creator_name')
                        ->label(__('issue.creator'))
                        ->content(auth()->user()->name),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\ImageColumn::make('currentTranslation.image')
                ->label('')
                ->disk('issue')
                ->height(100)
                ->width(70)
                ->defaultImageUrl(asset('images/default-cover.png'))
                ->square()
                ->grow(false),
            Tables\Columns\TextColumn::make('journal.code')->label(__('issue.journal')),
            Tables\Columns\TextColumn::make('volume')->label(__('issue.volume')),
            Tables\Columns\TextColumn::make('number')->label(__('issue.number')),
            Tables\Columns\TextColumn::make('year')->label(__('issue.year')),
            Tables\Columns\TextColumn::make('translations.title')->label(__('issue.title'))->limit(30),
        ])
            ->filters([])
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
            'index' => Pages\ListIssues::route('/'),
            'create' => Pages\CreateIssue::route('/create'),
            'view' => Pages\ViewIssue::route('/{record}'),
            'edit' => Pages\EditIssue::route('/{record}/edit'),
        ];
    }

    public function getTranslatedTitleAttribute()
    {
        return $this->translations
            ->where('locale', app()->getLocale())
            ->first()
            ->title ?? $this->translations->first()->title ?? '—';
    }

    protected static function generateSlug($get, $set): void
    {
        $journalCode = Journal::find($get('journal_id'))?->code ?? '';
        $volume = $get('volume') ?? '';
        $number = $get('number') ?? '';
        $year = $get('year') ?? '';

        if ($journalCode && $volume && $number && $year) {
            $slug = Str::slug("{$journalCode}-v{$volume}-n{$number}-{$year}");
            $set('slug', $slug);
        }
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        return ! ($user->hasRole('researcher') || $user->hasRole('reviewer'));
    }
}

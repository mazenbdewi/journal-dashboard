<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ArticleResource\Pages;
use App\Models\Article;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Unique;

class ArticleResource extends Resource
{
    protected static ?string $model = Article::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?int $navigationSort = 03;

    public static function form(Form $form): Form
    {
        $user = Auth::user();
        $shouldHidePublishTab = $user->hasRole('researcher') || $user->hasRole('reviewer') || $user->hasRole('user');

        return $form
            ->schema([
                Tabs::make(__('article.form_title'))
                    ->tabs([
                        Tab::make(__('article.tab_info'))
                            ->schema([
                                Forms\Components\Repeater::make('translations')
                                    ->label(__('article.basic_info'))
                                    ->relationship()
                                    ->disableItemDeletion()
                                    ->schema([
                                        Forms\Components\Select::make('locale')
                                            ->label(__('article.language'))
                                            ->options([
                                                'ar' => 'العربية',
                                                'en' => 'English',
                                            ])
                                            ->required()
                                            ->native(false),

                                        // Forms\Components\TextInput::make('title')
                                        //     ->label(__('article.title'))
                                        //     ->required()
                                        //     ->maxLength(255)
                                        //     ->unique(
                                        //         table: 'article_translations',
                                        //         column: 'title',
                                        //         ignoreRecord: true,
                                        //         modifyRuleUsing: function (Unique $rule, Get $get) {
                                        //             return $rule->where('locale', $get('locale'));
                                        //         }
                                        //     )
                                        //     ->validationMessages([
                                        //         'unique' => __('article.title_unique_error'),
                                        //     ]),

                                        Forms\Components\TextInput::make('title')
                                            ->label(__('article.title'))
                                            ->required()
                                            ->maxLength(255)
                                            ->unique(
                                                table: 'article_translations',
                                                column: 'title',
                                                ignoreRecord: true,
                                                modifyRuleUsing: function (Unique $rule, Get $get) {
                                                    return $rule->where('locale', $get('locale'));
                                                }
                                            )
                                            ->afterStateUpdated(function ($state, callable $set) {
                                                if (! empty($state)) {
                                                    $slug = mb_strtolower($state); // حروف صغيرة
                                                    $slug = preg_replace('/[^\p{L}\p{N}]+/u', '-', $slug); // استبدال الرموز بمطابقة اللغة
                                                    $slug = trim($slug, '-'); // إزالة الشرطات الزائدة
                                                    $set('slug', $slug); // تعيين قيمة الـ slug
                                                }
                                            })
                                            ->validationMessages([
                                                'unique' => __('article.title_unique_error'),
                                            ])
                                            ->columnSpan(2),

                                        Forms\Components\Hidden::make('slug')
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->columnSpan(2),

                                        Forms\Components\Textarea::make('abstract')
                                            ->label(__('article.abstract'))
                                            ->rows(3),

                                        Forms\Components\RichEditor::make('content')
                                            ->label(__('article.content')),

                                        Forms\Components\TagsInput::make('keywords')
                                            ->label(__('article.keywords'))
                                            ->separator(','),
                                    ])
                                    ->columns(2)
                                    ->defaultItems(1)
                                    ->collapsible()
                                    ->itemLabel(fn (array $state): ?string => $state['title'] ?? __('article.new_translation'))
                                    ->addActionLabel(__('article.add_language')),
                            ]),
                        Tab::make(__('article.tab_authors'))
                            ->schema([
                                Forms\Components\Repeater::make('articleAuthors')
                                    ->relationship()
                                    ->label(__('article.authors'))
                                    ->addActionLabel(__('article.add_author'))
                                    ->defaultItems(1)
                                    ->columns(3)
                                    ->required()
                                    ->schema([
                                        Forms\Components\Radio::make('is_registered')
                                            ->label(__('article.author_type'))
                                            ->options([
                                                1 => __('article.registered_author'),
                                                0 => __('article.external_author'),
                                            ])
                                            ->default(0)
                                            ->inline()
                                            ->live(),

                                        Forms\Components\Select::make('user_id')
                                            ->label(__('article.registered_author_search'))
                                            ->searchable()
                                            ->getSearchResultsUsing(fn (string $search) => \App\Models\User::where('email', 'like', "%{$search}%")
                                                ->limit(10)
                                                ->pluck('email', 'id')
                                            )
                                            ->getOptionLabelUsing(fn ($value) => \App\Models\User::find($value)?->email)
                                            ->hidden(fn ($get) => $get('is_registered') != 1)
                                            ->required(fn ($get) => $get('is_registered') == 1),

                                        Forms\Components\TextInput::make('external_name')
                                            ->label(__('article.external_name'))
                                            ->placeholder(__('article.external_name_placeholder'))
                                            ->hidden(fn ($get) => $get('is_registered') == 1)
                                            ->required(fn ($get) => $get('is_registered') == 0),

                                        Forms\Components\TextInput::make('external_name_en')
                                            ->label(__('article.external_name_en'))
                                            ->placeholder(__('article.external_name_placeholder_en'))
                                            ->hidden(fn ($get) => $get('is_registered') == 1)
                                            ->required(fn ($get) => $get('is_registered') == 0),

                                        Forms\Components\TextInput::make('external_email')
                                            ->label(__('article.email'))
                                            ->email()
                                            ->hidden(fn ($get) => $get('is_registered') == 1),

                                        Forms\Components\TextInput::make('external_affiliation')
                                            ->label(__('article.affiliation'))
                                            ->hidden(fn ($get) => $get('is_registered') == 1),

                                        Forms\Components\Toggle::make('is_main_author')
                                            ->label(__('article.is_main_author'))
                                            ->default(false),
                                    ]),
                            ]),

                        Tab::make(__('article.tab_files'))
                            ->schema([
                                Forms\Components\Repeater::make('revisions')
                                    ->label(__('article.article_files'))
                                    ->relationship()
                                    ->disableItemDeletion()
                                    ->schema([
                                        FileUpload::make('file_path')
                                            ->label(__('article.upload_file'))
                                            ->required()
                                            ->disk('public')
                                            ->directory('articles')
                                            ->preserveFilenames()
                                            ->openable()
                                            ->downloadable(),

                                        Forms\Components\TextInput::make('note')
                                            ->label(__('article.note'))
                                            ->placeholder(__('article.note_placeholder')),
                                        // ->columnSpanFull(),
                                        Forms\Components\Toggle::make('file_published')
                                            ->label(__('article.file_published'))
                                            ->visible(fn (): bool => ! Auth::user()->hasRole('researcher'))
                                            ->inline(false),
                                    ])
                                    ->defaultItems(0)
                                    // ->columnSpanFull()
                                    ->columns(2)
                                    ->addActionLabel(__('article.add_file')),
                            ]),
                        ...($shouldHidePublishTab ? [] : [
                            Tab::make(__('article.tab_publish'))
                                ->schema([
                                    Forms\Components\Grid::make(3)
                                        ->schema([
                                            Forms\Components\Select::make('issue_id')
                                                ->label(__('article.issue'))
                                                ->options(
                                                    \App\Models\Issue::with('translations')
                                                        ->get()
                                                        ->mapWithKeys(function ($issue) {
                                                            return [$issue->id => optional($issue->translations->first())->title ?? 'No Title'];
                                                        })
                                                )
                                                ->nullable()
                                                ->searchable(),

                                            Forms\Components\TextInput::make('doi')
                                                ->label(__('article.doi')),
                                            Forms\Components\Select::make('created_by')
                                                ->relationship('creator', 'name')
                                                ->searchable()
                                                ->default(Auth::id())
                                                ->disabled()
                                                ->dehydrated()
                                                ->preload()
                                                ->required(),

                                            Forms\Components\DatePicker::make('submission_at')
                                                ->label(__('article.submission_date'))
                                                ->disabled()
                                                ->default(now())
                                                ->native(false)
                                                ->displayFormat('d-m-yy')
                                                ->dehydrated(false),

                                            Forms\Components\DatePicker::make('acceptance_date')
                                                ->label(__('article.acceptance_date'))
                                                ->native(false)
                                                ->placeholder(__('article.acceptance_date'))
                                                ->displayFormat('d-m-yy'),

                                            Forms\Components\DatePicker::make('published_at')
                                                ->label(__('article.publication_date'))
                                                ->native(false)
                                                ->placeholder(__('article.publication_date'))
                                                ->displayFormat('d-m-yy'),

                                            Forms\Components\Select::make('status')
                                                ->label(__('article.status'))
                                                ->options([
                                                    'pending' => __('article.status_pending'),
                                                    'revoke' => __('article.status_revoke'),
                                                    'under_review' => __('article.status_under_review'),
                                                    'accepted' => __('article.status_accepted'),
                                                    'rejected' => __('article.status_rejected'),
                                                    'published' => __('article.status_published'),
                                                ])
                                                ->default('pending')
                                                ->required(),
                                            // ->disabled(fn (Get $get) => $get('status') === 'revoke'),
                                        ]),
                                ]),
                        ]),
                    ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('translations.title')
                    ->label(__('article.title'))
                    ->state(fn ($record) => $record->getTranslation(app()->getLocale())?->title)
                    ->limit(30)
                    ->searchable(),

                TextColumn::make('main_author_display')
                    ->label(__('article.main_author')),

                TextColumn::make('status')
                    ->label(__('article.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'under_review' => 'info',
                        'published' => 'success',
                        'revoke' => 'warning',
                        'rejected' => 'danger',
                        'accepted' => 'success',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => __('article.status_pending'),
                        'under_review' => __('article.status_under_review'),
                        'published' => __('article.status_published'),
                        'revoke' => __('article.status_revoke'),
                        'rejected' => __('article.status_rejected'),
                        'accepted' => __('article.status_accepted'),
                        default => $state,
                    }),

                TextColumn::make('submission_at')
                    ->label(__('article.submission_date'))
                    ->date('d-m-Y')
                    ->placeholder(__('article.not_set')),

                TextColumn::make('acceptance_at')
                    ->label(__('article.acceptance_date'))
                    ->date('d-m-Y')
                    ->placeholder(__('article.not_set')),

                TextColumn::make('published_at')
                    ->label(__('article.publication_date'))
                    ->date('d-m-Y')
                    ->placeholder(__('article.not_set')),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListArticles::route('/'),
            'create' => Pages\CreateArticle::route('/create'),
            'edit' => Pages\EditArticle::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('article.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('article.plural_model_label');
    }

    public static function getNavigationLabel(): string
    {
        return __('article.navigation_label');
    }

    public static function getNavigationGroup(): ?string
    {
        $user = auth()->user();

        if ($user->hasRole('researcher')) {
            return null;
        }

        return __('article.navigation_group');
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        return ! ($user->hasRole('reviewer'));
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = Auth::user();

        // إذا كان الباحث، عرض المقالات التي كتبها فقط
        if ($user->hasRole('researcher')) {
            $query->whereHas('articleAuthors', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        // إذا كان المراجع، يمكن إضافة شروط أخرى إذا لزم
        if ($user->hasRole('reviewer')) {
            $query->whereRaw('0 = 1'); // مثال: لا يعرض أي شيء
        }

        return $query;
    }

    public static function canDelete(Model $record): bool
    {
        // لا يمكن حذف المقال إذا كان له مهام مراجعة (أي تم إرساله للمراجعين)
        return ! $record->reviewAssignments()->exists();
    }
}

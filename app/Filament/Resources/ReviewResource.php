<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReviewResource\Pages;
use App\Filament\Resources\ReviewResource\RelationManagers\RevisionsRelationManager;
use App\Models\Article;
use App\Models\Review;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    public static function getNavigationGroup(): ?string
    {
        $user = auth()->user();

        if ($user->hasRole('researcher')) {
            return null;
        }

        return __('review_assignment.navigation_group');
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        return ! ($user->hasRole('researcher'));
    }

    public static function getModelLabel(): string
    {
        return __('review.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('review.plural_label');
    }

    public static function getNavigationLabel(): string
    {
        return __('review.navigation_label');
    }

    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                \Filament\Forms\Components\Grid::make()
                    ->schema([
                        \Filament\Forms\Components\Group::make()
                            ->schema([
                                Section::make('ملفات الباحث')
                                    ->schema([
                                        \Filament\Forms\Components\Grid::make()
                                            ->schema(function ($record) {
                                                if (! $record || ! $record->article) {
                                                    return [];
                                                }

                                                if (! $record->article->relationLoaded('revisions')) {
                                                    $record->article->load('revisions');
                                                }

                                                $components = [];
                                                foreach ($record->article->revisions as $index => $revision) {
                                                    $components[] = \Filament\Forms\Components\Card::make()
                                                        ->schema([
                                                            \Filament\Forms\Components\Actions::make([
                                                                \Filament\Forms\Components\Actions\Action::make('download_'.$index)
                                                                    ->label('تحميل الملف')
                                                                    ->url(asset('storage/'.$revision->file_path))
                                                                    ->openUrlInNewTab()
                                                                    ->button()
                                                                    ->icon('heroicon-o-arrow-down-tray')
                                                                    ->visible((bool) $revision->file_path),
                                                            ]),
                                                            \Filament\Forms\Components\Placeholder::make('note_'.$index)
                                                                ->label('ملاحظة')
                                                                ->content($revision->note ?? 'لا توجد ملاحظة'),
                                                            \Filament\Forms\Components\Placeholder::make('date_'.$index)
                                                                ->label('تاريخ الرفع')
                                                                ->content($revision->created_at->format('Y-m-d')),
                                                        ])
                                                        ->columnSpan(1);
                                                }

                                                return $components;
                                            })
                                            ->columns(1)
                                            ->columnSpanFull(),
                                    ])
                                    ->columnSpanFull()
                                    ->collapsible(),

                            ])
                            ->columnSpan(['lg' => 3]),
                        \Filament\Forms\Components\Group::make()
                            ->schema([
                                Section::make(__('review.review_info'))
                                    ->schema([
                                        Select::make('article_id')
                                            ->label(__('review.article'))
                                            ->relationship(
                                                name: 'article',
                                                titleAttribute: 'id',
                                                modifyQueryUsing: function (Builder $query) {
                                                    $query->with(['translations' => function ($q) {
                                                        $q->whereIn('locale', ['ar', 'en']);
                                                    }]);

                                                    $reviewedArticleIds = Review::pluck('article_id');
                                                    $query->whereNotIn('id', $reviewedArticleIds);

                                                    if (! Auth::user()->hasRole('super_admin')) {
                                                        $query->whereHas('reviewAssignments', function ($q) {
                                                            $q->where('reviewer_id', Auth::id());
                                                        });
                                                    }
                                                }
                                            )
                                            ->getOptionLabelFromRecordUsing(fn (Article $record) => $record->getDualTitle())
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->columnSpanFull()
                                            ->visible(fn (string $context) => $context === 'create')
                                            ->rules([
                                                function (Get $get) {
                                                    return function (string $attribute, $value, \Closure $fail) use ($get) {
                                                        $exists = Review::where('article_id', $value)
                                                            ->when($get('id'), fn ($q) => $q->where('id', '!=', $get('id')))
                                                            ->exists();

                                                        if ($exists) {
                                                            $fail(__('review.article_already_reviewed'));
                                                        }
                                                    };
                                                },
                                            ]),
                                        Placeholder::make('article_title')
                                            ->label(__('review.article'))
                                            ->content(fn ($record) => $record?->article?->getDualTitle())
                                            ->visible(fn (string $context) => $context === 'edit')
                                            ->columnSpanFull(),
                                        Select::make('reviewer_id')
                                            ->label(__('review.reviewer'))
                                            ->options(function () {
                                                $query = User::whereHas('roles', fn ($q) => $q->where('name', 'reviewer'));

                                                if (Auth::user()->hasRole('reviewer')) {
                                                    $query->where('id', Auth::id());
                                                }

                                                return $query->pluck('name', 'id');
                                            })
                                            ->default(fn () => Auth::user()->hasRole('reviewer') ? Auth::id() : null)
                                            ->disabled(fn () => Auth::user()->hasRole('reviewer'))
                                            ->dehydrated(fn () => true)
                                            ->required()
                                            ->searchable()
                                            ->preload(),
                                        Forms\Components\DatePicker::make('review_date')
                                            ->label(__('review.review_date'))
                                            ->required()
                                            ->native(false)
                                            ->displayFormat('d-m-yy')
                                            ->default(now()),
                                        Select::make('status')
                                            ->label(__('review.status'))
                                            ->options([
                                                'pending' => __('review.pending'),
                                                'under_review' => __('review.under_review'),
                                                'published' => __('review.published'),
                                                'revoke' => __('review.revoke'),
                                                'rejected' => __('review.rejected'),
                                                'accepted' => __('review.accepted'),
                                            ])
                                            ->required()
                                            ->default('pending')
                                            ->native(false)
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, $livewire) {
                                                if ($livewire instanceof Pages\EditReview) {
                                                    $livewire->dispatch('statusUpdated', $state);
                                                }
                                            }),
                                        Select::make('decision')
                                            ->label(__('review.decision'))
                                            ->options([
                                                'accept' => __('review.accept'),
                                                'minor_revision' => __('review.minor_revision'),
                                                'major_revision' => __('review.major_revision'),
                                                'reject' => __('review.reject'),
                                                'withdrawn' => __('review.withdrawn'),
                                            ])
                                            ->native(false)
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, $livewire) {
                                                if ($livewire instanceof Pages\EditReview) {
                                                    $livewire->dispatch('decisionUpdated', $state);
                                                }
                                            }),
                                        Textarea::make('editor_notes')
                                            ->label(__('review.editor_notes'))
                                            ->columnSpanFull(),

                                    ])->columns(2),

                                Repeater::make('evaluation')
                                    ->label(__('review.evaluation_section'))
                                    ->relationship('evaluation')
                                    ->maxItems(1)
                                    ->addActionLabel(__('review.add_evaluation'))
                                    ->schema([
                                        Section::make(__('review.basic_info_section'))
                                            ->description(__('review.basic_info_description'))
                                            ->schema([
                                                Group::make()
                                                    ->schema([
                                                        Toggle::make('page_count_appropriate')
                                                            ->label(__('review.page_count_appropriate')),
                                                        Textarea::make('page_count_comment')
                                                            ->label(__('review.page_count_comment'))
                                                            ->placeholder(__('review.page_count_comment')),
                                                    ])
                                                    ->columns(2),
                                                Group::make()
                                                    ->schema([
                                                        Toggle::make('titles_match_languages')
                                                            ->label(__('review.titles_match_languages')),
                                                        Textarea::make('titles_match_comment')
                                                            ->label(__('review.titles_match_comment'))
                                                            ->placeholder(__('review.titles_match_comment')),
                                                    ])
                                                    ->columns(2),
                                            ])
                                            ->collapsible(),

                                        Section::make(__('review.objectives_references_section'))
                                            ->description(__('review.objectives_references_description'))
                                            ->schema([
                                                Group::make()
                                                    ->schema([
                                                        Toggle::make('objective_clearly_defined')
                                                            ->label(__('review.objective_clearly_defined')),
                                                        Toggle::make('objective_achieved')
                                                            ->label(__('review.objective_achieved')),
                                                        Textarea::make('objective_comment')
                                                            ->label(__('review.objective_comment'))
                                                            ->placeholder(__('review.objective_comment'))
                                                            ->columnSpanFull(),
                                                    ]),
                                                Group::make()
                                                    ->schema([
                                                        Toggle::make('all_relevant_references')
                                                            ->label(__('review.all_relevant_references')),
                                                        Toggle::make('references_are_recent')
                                                            ->label(__('review.references_are_recent')),
                                                        Textarea::make('references_comment')
                                                            ->label(__('review.references_comment'))
                                                            ->placeholder(__('review.references_comment'))
                                                            ->columnSpanFull(),
                                                    ]),
                                            ])
                                            ->collapsible(),

                                        Section::make(__('review.scientific_value_section'))
                                            ->description(__('review.scientific_value_description'))
                                            ->schema([
                                                Group::make()
                                                    ->schema([
                                                        Select::make('scientific_value')
                                                            ->label(__('review.scientific_value'))
                                                            ->options([
                                                                'new_theory_new_results' => __('review.new_theory_new_results'),
                                                                'known_theory_new_results' => __('review.known_theory_new_results'),
                                                                'known_theory_known_results' => __('review.known_theory_known_results'),
                                                                'known_theory_strange_results' => __('review.known_theory_strange_results'),
                                                                'strange_theory_strange_results' => __('review.strange_theory_strange_results'),
                                                            ])
                                                            ->native(false)
                                                            ->columnSpan(2),
                                                        Toggle::make('published_before')
                                                            ->label(__('review.published_before'))
                                                            ->inline(false)
                                                            ->columnSpan(1),
                                                        Textarea::make('published_before_comment')
                                                            ->label(__('review.published_before_comment'))
                                                            ->placeholder(__('review.published_before_comment'))
                                                            ->columnSpanFull(),
                                                    ])
                                                    ->columns(3),
                                                Group::make()
                                                    ->schema([
                                                        Toggle::make('results_verifiable')
                                                            ->label(__('review.results_verifiable')),
                                                        Toggle::make('results_well_documented')
                                                            ->label(__('review.results_well_documented')),
                                                        Toggle::make('results_scientifically_acceptable')
                                                            ->label(__('review.results_scientifically_acceptable')),
                                                        Textarea::make('results_comment')
                                                            ->label(__('review.results_comment'))
                                                            ->placeholder(__('review.results_comment'))
                                                            ->columnSpanFull(),
                                                    ]),

                                            ])
                                            ->collapsible(),

                                        Section::make(__('review.methodology_significance_section'))
                                            ->description(__('review.methodology_significance_description'))
                                            ->schema([
                                                Group::make()
                                                    ->schema([
                                                        TextInput::make('research_methodology')
                                                            ->label(__('review.research_methodology'))
                                                            ->placeholder(__('review.research_methodology')),
                                                        Toggle::make('methodology_suitable')
                                                            ->label(__('review.methodology_suitable'))
                                                            ->inline(false),
                                                        Textarea::make('methodology_comment')
                                                            ->label(__('review.methodology_comment'))
                                                            ->placeholder(__('review.methodology_comment'))
                                                            ->columnSpanFull(),
                                                    ]),
                                                Group::make()
                                                    ->schema([
                                                        CheckboxList::make('research_significance')
                                                            ->label(__('review.research_significance'))
                                                            ->options([
                                                                'knowledge_value' => __('review.knowledge_value'),
                                                                'useful_for_grads' => __('review.useful_for_grads'),
                                                                'applied_interest' => __('review.applied_interest'),
                                                                'bad' => __('review.bad'),
                                                            ])
                                                            ->columns(3)
                                                            ->bulkToggleable(),
                                                        Textarea::make('if_weak_comment')
                                                            ->label(__('review.if_weak_comment'))
                                                            ->placeholder(__('review.if_weak_comment'))
                                                            ->columnSpanFull(),
                                                    ]),
                                            ])
                                            ->collapsible(),

                                        Section::make(__('review.final_recommendations_section'))
                                            ->description(__('review.final_recommendations_description'))
                                            ->schema([
                                                Textarea::make('comments_for_author')
                                                    ->label(__('review.comments_for_author'))
                                                    ->placeholder(__('review.comments_for_author'))
                                                    ->rows(3)
                                                    ->columnSpanFull(),
                                                Select::make('research_type')
                                                    ->label(__('review.research_type'))
                                                    ->options([
                                                        'original' => __('review.original'),
                                                        'not_original' => __('review.not_original'),
                                                    ])
                                                    ->required()
                                                    ->native(false),
                                            ])
                                            ->collapsible(),
                                        section::make('رفع ملف')
                                            ->description()
                                            ->schema([
                                                FileUpload::make('file_path')
                                                    ->label('الملف التوضيحي')
                                                    ->directory('evaluation_files')
                                                    ->preserveFilenames()
                                                    ->openable()
                                                    ->downloadable()
                                                    ->columnSpanFull(),

                                                Textarea::make('note')
                                                    ->label('ملاحظة مرافقة للملف')
                                                    ->rows(2)
                                                    ->placeholder('شرح المحتوى أو سبب رفع هذا الملف')
                                                    ->columnSpanFull(),

                                            ])->collapsible(),
                                    ])
                                    ->columnSpanFull(),
                            ])
                            ->columnSpan(['lg' => 9]),
                    ])
                    ->columns(12)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('article.title')
                    ->label(__('review.article'))
                    ->sortable()
                    ->searchable()
                    ->description(fn ($record) => $record->article?->getEnglishTitle() ?? ''),

                Tables\Columns\TextColumn::make('article.status')
                    ->label('حالة المقالة')
                    ->badge()
                    ->color(fn ($state) => $state === 'revoke' || $state === 'rejected' ? 'danger' : 'success')
                    ->formatStateUsing(fn ($state) => $state === 'revoke' || $state === 'rejected' ? 'مجمدة' : 'نشطة'),

                Tables\Columns\TextColumn::make('reviewer.name')
                    ->label(__('review.reviewer'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('review.status'))
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'under_review' => 'warning',
                        'published' => 'success',
                        'revoke' => 'danger',
                        'rejected' => 'danger',
                        'accepted' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => __("review.{$state}")),

                Tables\Columns\TextColumn::make('decision')
                    ->label(__('review.decision'))
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'accept' => 'success',
                        'minor_revision' => 'info',
                        'major_revision' => 'warning',
                        'reject' => 'danger',
                        'withdrawn' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => $state ? __("review.{$state}") : '-'),

                Tables\Columns\TextColumn::make('review_date')
                    ->label(__('review.review_date'))
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('review.status'))
                    ->options([
                        'pending' => __('review.pending'),
                        'under_review' => __('review.under_review'),
                        'published' => __('review.published'),
                        'revoke' => __('review.revoke'),
                        'rejected' => __('review.rejected'),
                        'accepted' => __('review.accepted'),
                    ]),
                Tables\Filters\SelectFilter::make('decision')
                    ->label(__('review.decision'))
                    ->options([
                        'accept' => __('review.accept'),
                        'minor_revision' => __('review.minor_revision'),
                        'major_revision' => __('review.major_revision'),
                        'reject' => __('review.reject'),
                        'withdrawn' => __('review.withdrawn'),
                    ]),
                Tables\Filters\Filter::make('review_date')
                    ->label(__('review.review_date'))
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('من تاريخ'),
                        Forms\Components\DatePicker::make('until')->label('إلى تاريخ'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('review_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('review_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('المزيد')
                    ->icon('heroicon-o-pencil')
                    ->visible(fn ($record) => ! (
                        auth()->user()?->hasRole('reviewer') &&
                        $record->article?->status === 'revoke'
                    )),
            ])

            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->label('حذف المحدد'),
            ])
            ->emptyStateHeading('لا توجد مراجعات')
            ->emptyStateDescription('ابدأ بإنشاء مراجعة جديدة للمقالات.')
            ->emptyStateActions([
                Tables\Actions\Action::make('create')
                    ->label('إنشاء مراجعة')
                    ->url(route('filament.adminpanel.resources.reviews.create'))
                    ->icon('heroicon-o-plus'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RevisionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReviews::route('/'),
            'create' => Pages\CreateReview::route('/create'),
            'edit' => Pages\EditReview::route('/{record}/edit'),
        ];
    }

    // public static function getEloquentQuery(): Builder
    // {
    //     $query = parent::getEloquentQuery();

    //     if (Auth::user()->hasRole('reviewer')) {
    //         return $query->where('reviewer_id', Auth::id());
    //     }

    //     return $query;
    // }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        // إذا كان المشرف، يعرض كل شيء
        if ($user->hasRole('super_admin')) {
            return $query;
        }

        // إذا كان مراجعًا، يعرض فقط مراجعاته
        if ($user->hasRole('reviewer')) {
            return $query->where('reviewer_id', $user->id);
        }

        // أي دور آخر (مثل الطلاب)، لا يرى شيئًا
        return $query->whereRaw('0 = 1'); // تعيد صفوف صفرية
    }
}

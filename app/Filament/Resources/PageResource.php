<?php

namespace App\Filament\Resources;

use AmidEsfahani\FilamentTinyEditor\TinyEditor;
use App\Filament\Resources\PageResource\Pages;
use App\Models\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Unique;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function getNavigationLabel(): string
    {
        return __('page.navigation.label');
    }

    public static function getLabel(): string
    {
        return __('page.navigation.singular');
    }

    public static function getPluralLabel(): string
    {
        return __('page.navigation.plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('page.navigation.group');
    }

    protected static ?int $navigationSort = 04;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Toggle::make('active')
                ->label(__('page.form.active'))
                ->default(true),
            Forms\Components\Select::make('journal_id')
                ->label(__('page.form.journal'))
                ->options(function () {
                    return \App\Models\Journal::with('translations')->get()->mapWithKeys(function ($journal) {
                        $title = $journal->currentTranslation?->title ?? $journal->translations->first()?->title ?? 'غير معروف';

                        return [$journal->id => $title];
                    })->toArray();
                })
                ->getOptionLabelUsing(function ($value) {
                    $journal = \App\Models\Journal::find($value);

                    return $journal ? $journal->currentTranslation?->title ?? $journal->translations->first()?->title : __('page.form.home_page');
                })
                ->searchable()
                ->preload()
                ->nullable()
                ->helperText(__('page.form.journal_helper')),

            Forms\Components\Repeater::make('translations')
                ->label(__('page.form.translations'))
                ->relationship()
                ->schema([
                    Forms\Components\Select::make('locale')
                        ->label(__('page.form.locale'))
                        ->options([
                            'ar' => 'العربية',
                            'en' => 'English',
                        ])
                        ->required(),

                    Forms\Components\TextInput::make('title')
                        ->label(__('page.form.title'))
                        ->required()
                        ->maxLength(255)
                        ->unique(
                            table: 'page_translations',
                            column: 'title',
                            modifyRuleUsing: function (Unique $rule, Get $get, $state) {
                                // استخراج ID سجل الترجمة الحالي
                                $id = $get('id');

                                // إذا كنا في حالة تعديل، تجاهل نفس السجل
                                if ($id) {
                                    $rule->ignore($id);
                                }

                                // إضافة شرط اللغة
                                return $rule->where('locale', $get('locale'));
                            }
                        )
                        ->validationMessages([
                            'unique' => __('page.form.title_unique_error'),
                        ])
                        ->afterStateUpdated(function ($state, callable $set) {
                            if (! empty($state)) {
                                $slug = mb_strtolower($state);
                                $slug = preg_replace('/[^\p{L}\p{N}]+/u', '-', $slug);
                                $slug = trim($slug, '-');
                                $set('slug', $slug);
                            }
                        }),

                    Forms\Components\Hidden::make('slug')
                        ->label(__('page.form.slug'))
                        ->required()
                        ->unique(
                            table: 'page_translations',
                            column: 'slug',
                            ignoreRecord: true,
                            modifyRuleUsing: function (Unique $rule, Get $get) {
                                return $rule->where('locale', $get('locale'));
                            }
                        )
                        ->columnSpan(2),
                    TinyEditor::make('content')
                        ->label(__('page.form.content'))
                        ->fileAttachmentsDisk('public')
                        ->fileAttachmentsVisibility('public')
                        ->fileAttachmentsDirectory('uploads')
                        ->profile('default')
                        ->direction('auto')
                        ->columnSpanFull(),

                    Forms\Components\FileUpload::make('file')
                        ->label(__('page.form.file'))
                        ->directory('pages')
                        ->preserveFilenames()
                        ->downloadable()
                        ->openable(),

                    Forms\Components\Textarea::make('meta_description')
                        ->label(__('page.form.meta_description'))
                        ->rows(2),

                    Forms\Components\TagsInput::make('keywords')
                        ->label(__('page.form.keywords'))
                        ->separator(','),
                ])
                ->columnSpanFull()
                ->createItemButtonLabel(__('page.form.add_translation')),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('translations.title')
                    ->label(__('page.table.title'))
                    ->limit(30),

                // عرض ونسخ الـ slug للغة العربية
                Tables\Columns\TextColumn::make('arabic_slug')
                    ->label('Slug (العربية)')
                    ->state(function ($record) {
                        $arabicTranslation = $record->translations->where('locale', 'ar')->first();

                        return $arabicTranslation ? $arabicTranslation->slug : '-';
                    })
                    ->copyable()
                    ->copyMessage('تم نسخ الرابط العربي')
                    ->copyMessageDuration(1500)
                    ->searchable(
                        query: function ($query, $search) {
                            $query->whereHas('translations', function ($q) use ($search) {
                                $q->where('locale', 'ar')
                                    ->where('slug', 'like', "%{$search}%");
                            });
                        }
                    ),

                // عرض ونسخ الـ slug للغة الإنجليزية
                Tables\Columns\TextColumn::make('english_slug')
                    ->label('Slug (English)')
                    ->state(function ($record) {
                        $englishTranslation = $record->translations->where('locale', 'en')->first();

                        return $englishTranslation ? $englishTranslation->slug : '-';
                    })
                    ->copyable()
                    ->copyMessage('English slug copied')
                    ->copyMessageDuration(1500)
                    ->searchable(
                        query: function ($query, $search) {
                            $query->whereHas('translations', function ($q) use ($search) {
                                $q->where('locale', 'en')
                                    ->where('slug', 'like', "%{$search}%");
                            });
                        }
                    ),

                Tables\Columns\BooleanColumn::make('active')
                    ->label(__('page.table.active')),

                Tables\Columns\TextColumn::make('created_at')
                    ->since()
                    ->label(__('page.table.created_at')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                // إضافة زر نسخ إضافي في Actions
                Tables\Actions\Action::make('copySlugs')
                    ->label('نسخ الروابط')
                    ->icon('heroicon-o-clipboard')
                    ->action(function ($record) {
                        $slugs = [];

                        $arabic = $record->translations->where('locale', 'ar')->first();
                        $english = $record->translations->where('locale', 'en')->first();

                        if ($arabic) {
                            $slugs[] = "العربية: {$arabic->slug}";
                        }

                        if ($english) {
                            $slugs[] = "English: {$english->slug}";
                        }

                        $text = implode('\n', $slugs);

                        // نسخ إلى الحافظة
                        echo "<script>
                            navigator.clipboard.writeText(`{$text}`).then(() => {
                                alert('تم نسخ جميع الروابط');
                            });
                        </script>";
                    }),
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
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        return ! ($user->hasRole('researcher') || $user->hasRole('reviewer'));
    }
}

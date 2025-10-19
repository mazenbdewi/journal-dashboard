<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReviewAssignmentResource\Pages;
use App\Mail\ReviewerAssignmentMail;
use App\Mail\ReviewerDecisionMail;
use App\Models\Article;
use App\Models\ReviewAssignment;
use App\Models\User;
use App\Notifications\ReviewerDecisionNotification;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ReviewAssignmentResource extends Resource
{
    protected static ?string $model = ReviewAssignment::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-plus';

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

    public static function getLabel(): string
    {
        return __('review_assignment.model_label');
    }

    public static function getPluralLabel(): string
    {
        return __('review_assignment.plural_label');
    }

    protected static ?int $navigationSort = 05;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('article_id')
                    ->label(__('review_assignment.article'))
                    ->relationship('article', 'id')
                    ->getOptionLabelFromRecordUsing(
                        fn (Article $record) => $record->title ?: __('review_assignment.article').' #'.$record->id
                    )
                    ->required(),

                Select::make('reviewer_id')
                    ->label(__('review_assignment.reviewer'))
                    ->options(function () {
                        return User::whereHas('roles', fn ($q) => $q->where('name', 'reviewer'))
                            ->get()
                            ->mapWithKeys(fn ($user) => [
                                $user->id => $user->name.' ('.$user->email.')',
                            ]);
                    })
                    ->searchable()
                    ->required()
                    ->preload(),

                DatePicker::make('assigned_at')
                    ->label(__('review_assignment.assigned_at'))
                    ->default(now())
                    ->native(false)
                    ->displayFormat('d-m-yy')
                    ->required(),

                DatePicker::make('deadline')
                    ->label(__('review_assignment.deadline'))
                    ->native(false)
                    ->displayFormat('d-m-yy')
                    ->placeholder(__('review_assignment.deadline_placeholder'))
                    ->required(),

                Select::make('status')
                    ->label(__('review_assignment.status'))
                    ->options([
                        'pending' => __('review_assignment.pending'),
                        'completed' => __('review_assignment.completed'),
                        'declined' => __('review_assignment.declined'),
                    ])
                    ->required(),
            ]);
    }

    protected static function sendAssignmentEmail(ReviewAssignment $reviewAssignment): void
    {
        try {
            Mail::to($reviewAssignment->reviewer->email)
                ->send(new ReviewerAssignmentMail($reviewAssignment));
        } catch (\Exception $e) {
            \Log::error('Failed to send reviewer assignment email: '.$e->getMessage());
        }
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('article.title')
                    ->label(__('review_assignment.article'))
                    ->getStateUsing(fn (ReviewAssignment $record) => $record->article->title),

                Tables\Columns\TextColumn::make('reviewer.name')
                    ->label(__('review_assignment.reviewer'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('review_assignment.status'))
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pending' => __('review_assignment.pending'),
                        'completed' => __('review_assignment.completed'),
                        'declined' => __('review_assignment.declined'),
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('deadline')
                    ->label(__('review_assignment.deadline'))
                    ->date('d-m-Y'),
            ])
            ->actions([
                Tables\Actions\Action::make('download_article')
                    ->label(__('review_assignment.download_article'))
                    ->icon('heroicon-o-arrow-down')
                    ->button()
                    ->color('primary')
                    ->url(fn (ReviewAssignment $record) => $record->article->revisions()->latest()->first()
                      ? asset('storage/'.$record->article->revisions()->latest()->first()->file_path)
                      : '#')
                    ->openUrlInNewTab(true)
                    ->visible(fn (ReviewAssignment $record) => $record->article->revisions()->exists()),

                Tables\Actions\Action::make('accept')
                    ->label(__('review_assignment.accept'))
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->action(function (ReviewAssignment $record) {
                        $record->status = 'completed';
                        $record->save();

                        Log::info('Review assignment accepted', [
                            'assignment_id' => $record->id,
                            'reviewer_id' => $record->reviewer_id,
                            'article_id' => $record->article_id,
                        ]);

                        // إرسال إشعار إلى الأدمن
                        static::sendReviewerDecisionNotification($record, 'accepted');
                    })
                    ->visible(fn (ReviewAssignment $record): bool => $record->reviewer_id === Auth::id() &&
                        $record->status === 'pending' &&
                        Auth::user()->hasRole('reviewer')
                    ),

                Tables\Actions\Action::make('decline')
                    ->label(__('review_assignment.decline'))
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->action(function (ReviewAssignment $record) {
                        $record->status = 'declined';
                        $record->save();

                        Log::info('Review assignment declined', [
                            'assignment_id' => $record->id,
                            'reviewer_id' => $record->reviewer_id,
                            'article_id' => $record->article_id,
                        ]);

                        static::sendReviewerDecisionNotification($record, 'declined');
                    })
                    ->visible(fn (ReviewAssignment $record): bool => $record->reviewer_id === Auth::id() &&
                        $record->status === 'pending' &&
                        Auth::user()->hasRole('reviewer')
                    ),

                Tables\Actions\ViewAction::make()
                    ->visible(fn (): bool => ! Auth::user()->hasRole('reviewer')),

                Tables\Actions\EditAction::make()
                    ->visible(fn (): bool => ! Auth::user()->hasRole('reviewer')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn (): bool => ! Auth::user()->hasRole('reviewer')),
                ]),
            ]);
    }

    /**
     * إرسال إشعار عند قبول أو رفض المحكم للطلب
     */
    // protected static function sendReviewerDecisionNotification(ReviewAssignment $reviewAssignment, string $decision): void
    // {
    //     try {
    //         // الحصول على جميع الأدمن
    //         $admins = User::whereHas('roles', function ($query) {
    //             $query->where('name', 'admin');
    //         })->get();

    //         // إرسال الإشعار لكل أدمن
    //         foreach ($admins as $admin) {
    //             $admin->notify(new ReviewerDecisionNotification($reviewAssignment, $decision));
    //         }

    //     } catch (\Exception $e) {
    //         \Log::error('Failed to send reviewer decision notification: '.$e->getMessage());
    //     }
    // }

    protected static function sendReviewerDecisionNotification(ReviewAssignment $reviewAssignment, string $decision): void
    {
        try {
            Log::info('Starting to send reviewer decision notification', [
                'assignment_id' => $reviewAssignment->id,
                'decision' => $decision,
            ]);

            // الحصول على جميع الأدمن
            $admins = User::whereHas('roles', function ($query) {
                $query->where('name', 'super_admin');
            })->get();

            Log::info('Found admins for notification', [
                'admin_count' => $admins->count(),
                'admins' => $admins->pluck('email')->toArray(),
            ]);

            if ($admins->isEmpty()) {
                Log::warning('No admin users found to send notification to');

                return;
            }

            // إرسال الإشعار لكل أدمن
            foreach ($admins as $admin) {
                try {
                    Log::info('Sending notification to admin', [
                        'admin_id' => $admin->id,
                        'admin_email' => $admin->email,
                    ]);
                    // إرسال الإشعار في قاعدة البيانات
                    $admin->notify(new \App\Notifications\ReviewerDecisionNotification($reviewAssignment, $decision));

                    // إرسال الإيميل
                    Mail::to($admin->email)->send(new ReviewerDecisionMail($reviewAssignment, $decision));

                    Log::info('Notification sent successfully to admin', [
                        'admin_id' => $admin->id,
                    ]);

                } catch (\Exception $e) {
                    Log::error('Failed to send notification to admin: '.$e->getMessage(), [
                        'admin_id' => $admin->id,
                        'error' => $e->getTraceAsString(),
                    ]);
                }
            }

            Log::info('Reviewer decision notification process completed');

        } catch (\Exception $e) {
            Log::error('Failed to send reviewer decision notification: '.$e->getMessage(), [
                'error_trace' => $e->getTraceAsString(),
            ]);
        }
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['article', 'reviewer']);
    }

    public static function canCreate(): bool
    {
        return ! Auth::user()->hasRole('reviewer');
    }

    public static function canEdit($record): bool
    {
        return ! Auth::user()->hasRole('reviewer');
    }

    public static function canDelete($record): bool
    {
        return ! Auth::user()->hasRole('reviewer');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReviewAssignments::route('/'),
            'create' => Pages\CreateReviewAssignment::route('/create'),
            'view' => Pages\ViewReviewAssignment::route('/{record}'),
            'edit' => Pages\EditReviewAssignment::route('/{record}/edit'),
        ];
    }

    public static function getReplySchema(): array
    {
        return [
            Hidden::make('id'),

            TextInput::make('score')
                ->label(__('review_assignment.score'))
                ->numeric()
                ->minValue(1)
                ->maxValue(5)
                ->required(),

            Select::make('recommendation')
                ->label(__('review_assignment.recommendation'))
                ->options([
                    'accept' => __('review_assignment.accept'),
                    'revise' => __('review_assignment.revise'),
                    'reject' => __('review_assignment.reject'),
                ])
                ->required(),

            Textarea::make('comments_to_author')
                ->label(__('review_assignment.comments_to_author'))
                ->columnSpanFull(),

            Textarea::make('comments_to_editor')
                ->label(__('review_assignment.comments_to_editor'))
                ->columnSpanFull(),

            DatePicker::make('reviewed_at')
                ->label(__('review_assignment.reviewed_at'))
                ->required()
                ->columnSpan(1),
        ];
    }
}

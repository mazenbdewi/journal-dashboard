<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class Notifications extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-bell';

    protected static string $view = 'filament.pages.notifications';

    protected static ?int $navigationSort = 5;

    public function getTitle(): string
    {
        return __('notifications.title');
    }

    public static function getNavigationLabel(): string
    {
        $count = auth()->check() ? auth()->user()->unreadNotifications()->count() : 0;

        return $count > 0
            ? __('notifications.navigation_label').' ('.$count.')'
            : __('notifications.navigation_label');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::check();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Auth::user()->notifications()->getQuery()
            )
            ->columns([

                Tables\Columns\TextColumn::make('data.message')
                    ->label(__('notifications.title'))
                    // ->limit(100)
                    ->wrap()
                    ->tooltip(function ($record) {
                        return $record->data['message'] ?? '';
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('notifications.created_at'))
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),

                Tables\Columns\IconColumn::make('read_at')
                    ->label(__('notifications.read_status'))
                    ->getStateUsing(fn ($record) => is_null($record->read_at))
                    ->boolean()
                    ->trueIcon('heroicon-o-bell-alert')
                    ->falseIcon('heroicon-o-check-badge')
                    ->trueColor('warning')
                    ->falseColor('success'),
            ])
            ->actions([
                Tables\Actions\Action::make('mark_as_read')
                    ->label(__('notifications.mark_as_read'))
                    ->icon('heroicon-o-check')
                    ->action(function ($record) {
                        $record->markAsRead();
                        $this->dispatch('refresh');
                    })
                    ->hidden(fn ($record) => ! is_null($record->read_at)),

                Tables\Actions\Action::make('view')
                    ->label(__('notifications.view'))
                    ->icon('heroicon-o-eye')
                    ->url(function ($record) {
                        $url = is_array($record->data) ? ($record->data['url'] ?? '#') : '#';

                        return $url !== '#' ? $url : null;
                    })
                    ->openUrlInNewTab()
                    ->hidden(fn ($record) => ! isset($record->data['url']) || $record->data['url'] === '#'
                    ),

                Tables\Actions\Action::make('delete')
                    ->label(__('notifications.delete'))
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->action(function ($record) {
                        $record->delete();
                        $this->dispatch('refresh');
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('mark_as_read')
                        ->label(__('notifications.bulk_mark_as_read'))
                        ->icon('heroicon-o-check')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->markAsRead();
                            }
                            $this->dispatch('refresh');
                        }),

                    Tables\Actions\BulkAction::make('delete')
                        ->label(__('notifications.bulk_delete'))
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->delete();
                            }
                            $this->dispatch('refresh');
                        }),
                ]),
            ])
            ->emptyStateHeading(__('notifications.empty_heading'))
            ->emptyStateDescription(__('notifications.empty_description'))
            ->emptyStateIcon('heroicon-o-bell');
    }

    public function markAllAsRead(): void
    {
        Auth::user()->unreadNotifications->markAsRead();
        $this->dispatch('refresh');
    }

    public function getNotificationsCount(): int
    {
        return Auth::user()->unreadNotifications->count();
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('mark_all_as_read')
                ->label(__('notifications.mark_all_as_read'))
                ->icon('heroicon-o-check-badge')
                ->action('markAllAsRead')
                ->color('success')
                ->hidden(fn () => $this->getNotificationsCount() === 0),
        ];
    }
}

<?php

namespace App\Filament\Resources\ArticleResource\Pages;

use App\Filament\Resources\ArticleResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditArticle extends EditRecord
{
    protected static string $resource = ArticleResource::class;

    protected function getHeaderActions(): array
    {
        $record = $this->record;

        return [
            // زر الحذف يظهر فقط إذا لم تكن المقالة منشورة أو مسحوبة، ولمن لديهم الصلاحية
            Actions\DeleteAction::make()
                ->visible(fn () => ($record->status !== 'published' && $record->status !== 'revoke') &&
                    (Auth::user()->hasRole('super_admin') || Auth::user()->hasRole('researcher'))
                ),

            // زر الانسحاب يظهر فقط للباحثين، وعند عدم كون المقالة منشورة أو مسحوبة
            Action::make('withdraw')
                ->label(__('article.withdraw_button'))
                ->icon('heroicon-o-arrow-uturn-left')
                ->requiresConfirmation()
                ->modalHeading(__('article.withdraw_confirm_title'))
                ->modalSubheading(__('article.withdraw_confirm_message'))
                ->modalButton(__('article.withdraw_confirm_button'))
                ->action(function () use ($record) {
                    $record->update(['status' => 'revoke']);

                    Notification::make()
                        ->title(__('article.withdraw_success_title'))
                        ->success()
                        ->send();
                })
                ->color('warning')
                ->visible(fn () => Auth::user()->hasRole('researcher') && $record->status !== 'published' && $record->status !== 'revoke')
                ->disabled(fn () => $record->status === 'revoke'),
        ];
    }
}

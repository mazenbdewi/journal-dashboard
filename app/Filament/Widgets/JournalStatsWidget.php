<?php

namespace App\Filament\Widgets;

use App\Models\Issue;
use App\Models\Journal;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class JournalStatsWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected function getStats(): array
    {
        return [
            Stat::make(__('widgets.stats.total_journals'), Journal::count() - 1),
            Stat::make(__('widgets.stats.total_issues'), Issue::count()),
        ];
    }

    public static function canView(): bool
    {
        return Auth::user()->hasRole('super_admin');
    }
}

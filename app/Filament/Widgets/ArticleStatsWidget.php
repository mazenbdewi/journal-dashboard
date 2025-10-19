<?php

namespace App\Filament\Widgets;

use App\Models\Article;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class ArticleStatsWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected function getStats(): array
    {
        return [
            Stat::make(__('widgets.stats.total_articles'), Article::count()),
            Stat::make(__('widgets.stats.under_review'), Article::where('status', 'under_review')->count()),
            Stat::make(__('widgets.stats.accepted'), Article::where('status', 'accepted')->count()),
            Stat::make(__('widgets.stats.published'), Article::where('status', 'published')->count()),
            Stat::make(__('widgets.stats.rejected'), Article::where('status', 'rejected')->count()),
            Stat::make(__('widgets.stats.pending'), Article::where('status', 'pending')->count()),
            Stat::make(__('widgets.stats.revoke'), Article::where('status', 'revoke')->count()),
        ];
    }

    public static function canView(): bool
    {
        return Auth::user()->hasRole('super_admin');
    }
}

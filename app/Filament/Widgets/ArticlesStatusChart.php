<?php

namespace App\Filament\Widgets;

use App\Models\Article;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class ArticlesStatusChart extends ChartWidget
{
    protected static ?int $sort = 5;

    public function getHeading(): string
    {
        return __('widgets.articles_chart_title');
    }

    protected function getData(): array
    {
        return [
            'datasets' => [
                [
                    'label' => __('widgets.articles_chart_label'),
                    'data' => [
                        Article::where('status', 'pending')->count(),
                        Article::where('status', 'under_review')->count(),
                        Article::where('status', 'accepted')->count(),
                        Article::where('status', 'published')->count(),
                        Article::where('status', 'rejected')->count(),
                        Article::where('status', 'revoke')->count(),
                    ],
                    'backgroundColor' => [
                        '#ffeb3b',
                        '#2196f3',
                        '#4caf50',
                        '#673ab7',
                        '#f44336',
                        '#9e9e9e',
                    ],
                ],
            ],
            'labels' => [
                __('widgets.articles_chart_labels.pending'),
                __('widgets.articles_chart_labels.under_review'),
                __('widgets.articles_chart_labels.accepted'),
                __('widgets.articles_chart_labels.published'),
                __('widgets.articles_chart_labels.rejected'),
                __('widgets.articles_chart_labels.revoke'),
            ],
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }

    public static function canView(): bool
    {
        return Auth::user()->hasRole('super_admin');
    }
}

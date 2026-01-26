<?php

namespace Usamamuneerchaudhary\Notifier\Filament\Pages;

use Filament\Pages\Page;
use Usamamuneerchaudhary\Notifier\Filament\Widgets\NotificationAnalyticsChart;
use Usamamuneerchaudhary\Notifier\Filament\Widgets\NotificationChannelPerformance;
use Usamamuneerchaudhary\Notifier\Filament\Widgets\NotificationEngagementStats;
use Usamamuneerchaudhary\Notifier\Filament\Widgets\NotificationStatsOverview;
use Usamamuneerchaudhary\Notifier\Filament\Widgets\NotificationTimeSeriesChart;
use Usamamuneerchaudhary\Notifier\Filament\Widgets\RateLimitingStatusWidget;

class NotifierDashboard extends Page
{
    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?int $navigationSort = 0;
    protected string $view = 'notifier::pages.dashboard';

    public static function getNavigationGroup(): ?string
    {
        return __(config('notifier.defaults.navigation_group', 'Notifier'));
    }

    public static function getNavigationLabel(): string
    {
        return __('notifier::notifier.pages.dashboard.navigation_label');
    }

    public function getTitle(): string
    {
        return __('notifier::notifier.pages.dashboard.title');
    }


    protected function getWidgets(): array
    {
        return [
            NotificationStatsOverview::class,
            NotificationEngagementStats::class,
            NotificationTimeSeriesChart::class,
            NotificationAnalyticsChart::class,
            NotificationChannelPerformance::class,
            RateLimitingStatusWidget::class,
        ];
    }
}


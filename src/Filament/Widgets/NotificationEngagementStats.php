<?php

namespace Usamamuneerchaudhary\Notifier\Filament\Widgets;

use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Usamamuneerchaudhary\Notifier\Models\Notification;
use Usamamuneerchaudhary\Notifier\Models\NotificationSetting;

class NotificationEngagementStats extends BaseWidget
{
    protected ?string $pollingInterval = '30s';
    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        return false;
    }

    protected function getStats(): array
    {
        $analytics = NotificationSetting::getAnalytics();

        if (!($analytics['enabled'] ?? config('notifier.settings.analytics.enabled', true))) {
            return [
                Stat::make(__('notifier::notifier.widgets.engagement.analytics_disabled'), '')
                    ->description(__('notifier::notifier.widgets.engagement.enable_in_settings'))
                    ->color('gray'),
            ];
        }

        $totalSent = Notification::where('status', 'sent')->count();
        $totalOpened = Notification::whereNotNull('opened_at')->count();
        $totalClicked = Notification::whereNotNull('clicked_at')->count();

        $totalOpens = Notification::sum('opens_count');
        $totalClicks = Notification::sum('clicks_count');

        // Calculate engagement rates
        $openRate = $totalSent > 0 ? round(($totalOpened / $totalSent) * 100, 1) : 0;
        $clickRate = $totalSent > 0 ? round(($totalClicked / $totalSent) * 100, 1) : 0;
        $clickThroughRate = $totalOpened > 0 ? round(($totalClicked / $totalOpened) * 100, 1) : 0;

        return [
            Stat::make(__('notifier::notifier.widgets.engagement.total_opens'), number_format($totalOpens))
                ->description(__('notifier::notifier.widgets.engagement.unique_opens', ['count' => $totalOpened]))
                ->descriptionIcon('heroicon-m-eye')
                ->color('info')
                ->chart($this->getOpensChartData()),

            Stat::make(__('notifier::notifier.widgets.engagement.open_rate'), $openRate . '%')
                ->description(__('notifier::notifier.widgets.engagement.emails_opened', ['opened' => $totalOpened, 'sent' => $totalSent]))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make(__('notifier::notifier.widgets.engagement.total_clicks'), number_format($totalClicks))
                ->description(__('notifier::notifier.widgets.engagement.unique_clicks', ['count' => $totalClicked]))
                ->descriptionIcon('heroicon-m-cursor-arrow-rays')
                ->color('warning')
                ->chart($this->getClicksChartData()),

            Stat::make(__('notifier::notifier.widgets.engagement.click_rate'), $clickRate . '%')
                ->description(__('notifier::notifier.widgets.engagement.emails_clicked', ['clicked' => $totalClicked, 'sent' => $totalSent]))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('warning'),

            Stat::make(__('notifier::notifier.widgets.engagement.click_through_rate'), $clickThroughRate . '%')
                ->description(__('notifier::notifier.widgets.engagement.clicks_per_open'))
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('success'),
        ];
    }

    private function getOpensChartData(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $count = Notification::whereDate('opened_at', $date)
                ->sum('opens_count');
            $data[] = $count;
        }
        return $data;
    }

    private function getClicksChartData(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $count = Notification::whereDate('clicked_at', $date)
                ->sum('clicks_count');
            $data[] = $count;
        }
        return $data;
    }
}


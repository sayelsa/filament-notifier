<?php
namespace Usamamuneerchaudhary\Notifier\Filament\Widgets;

use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Usamamuneerchaudhary\Notifier\Models\Notification;
use Usamamuneerchaudhary\Notifier\Models\NotificationChannel;
use Usamamuneerchaudhary\Notifier\Services\ChannelService;

class NotificationStatsOverview extends BaseWidget
{
    protected ?string $pollingInterval = '10s';

    public static function canView(): bool
    {
        return false;
    }

    protected function getStats(): array
    {
        $totalNotifications = Notification::count();
        $sentNotifications = Notification::where('status', 'sent')->count();
        $pendingNotifications = Notification::where('status', 'pending')->count();
        $failedNotifications = Notification::where('status', 'failed')->count();
        $activeChannels = app(ChannelService::class)->getActiveChannels()->count();

        // Calculate success rate
        $successRate = $totalNotifications > 0 ? round(($sentNotifications / $totalNotifications) * 100, 1) : 0;

        // Get recent notifications for chart data
        $recentNotifications = $this->getRecentNotificationsData();

        return [
            Stat::make(__('notifier::notifier.widgets.overview.total'), $totalNotifications)
                ->description(__('notifier::notifier.widgets.overview.all_time'))
                ->descriptionIcon('heroicon-m-envelope')
                ->chart($recentNotifications)
                ->color('success'),

            Stat::make(__('notifier::notifier.widgets.overview.success_rate'), $successRate . '%')
                ->description(__('notifier::notifier.widgets.overview.sent_successfully', ['sent' => $sentNotifications, 'total' => $totalNotifications]))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make(__('notifier::notifier.widgets.overview.pending'), $pendingNotifications)
                ->description(__('notifier::notifier.widgets.overview.awaiting'))
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make(__('notifier::notifier.widgets.overview.failed'), $failedNotifications)
                ->description(__('notifier::notifier.widgets.overview.failed_delivery'))
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),

            Stat::make(__('notifier::notifier.widgets.overview.active_channels'), $activeChannels)
                ->description(__('notifier::notifier.widgets.overview.enabled_channels'))
                ->descriptionIcon('heroicon-m-bolt')
                ->color('info'),
        ];
    }

    private function getRecentNotificationsData(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $count = Notification::whereDate('created_at', $date)->count();
            $data[] = $count;
        }
        return $data;
    }

}

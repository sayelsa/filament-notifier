<?php

namespace Usamamuneerchaudhary\Notifier\Filament\Widgets;

use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Usamamuneerchaudhary\Notifier\Models\Notification;
use Usamamuneerchaudhary\Notifier\Models\NotificationChannel;
use Usamamuneerchaudhary\Notifier\Services\ChannelService;

class NotificationChannelPerformance extends ChartWidget
{
    protected static ?int $sort = 5;
    protected ?string $pollingInterval = '30s';

    public function getHeading(): ?string
    {
        return __('notifier::notifier.widgets.performance.heading');
    }

    public static function canView(): bool
    {
        return false;
    }

    protected function getData(): array
    {
        $channels = app(ChannelService::class)->getActiveChannels();
        $labels = [];
        $sentData = [];
        $openedData = [];
        $clickedData = [];
        $colors = [
            'rgba(59, 130, 246, 0.8)',
            'rgba(16, 185, 129, 0.8)',
            'rgba(245, 158, 11, 0.8)',
            'rgba(239, 68, 68, 0.8)',
            'rgba(139, 92, 246, 0.8)',
            'rgba(236, 72, 153, 0.8)',
        ];

        foreach ($channels as $index => $channel) {
            $labels[] = $channel->title;

            $sent = Notification::where('channel', $channel->type)
                ->where('status', 'sent')
                ->count();
            $opened = Notification::where('channel', $channel->type)
                ->whereNotNull('opened_at')
                ->count();
            $clicked = Notification::where('channel', $channel->type)
                ->whereNotNull('clicked_at')
                ->count();

            $sentData[] = $sent;
            $openedData[] = $opened;
            $clickedData[] = $clicked;
        }

        return [
            'datasets' => [
                [
                    'label' => __('notifier::notifier.widgets.performance.sent'),
                    'data' => $sentData,
                    'backgroundColor' => $colors[0],
                ],
                [
                    'label' => __('notifier::notifier.widgets.performance.opened'),
                    'data' => $openedData,
                    'backgroundColor' => $colors[1],
                ],
                [
                    'label' => __('notifier::notifier.widgets.performance.clicked'),
                    'data' => $clickedData,
                    'backgroundColor' => $colors[2],
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
            ],
        ];
    }
}


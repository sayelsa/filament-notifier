<?php

namespace Usamamuneerchaudhary\Notifier\Filament\Widgets;

use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Usamamuneerchaudhary\Notifier\Models\NotificationSetting;
use Usamamuneerchaudhary\Notifier\Services\RateLimitingService;

class RateLimitingStatusWidget extends BaseWidget
{
    protected static ?int $sort = 6;
    protected ?string $pollingInterval = '10s';

    public function getHeading(): ?string
    {
        return __('notifier::notifier.widgets.rate_limiting.heading');
    }

    public static function canView(): bool
    {
        return false;
    }

    protected function getStats(): array
    {
        $rateLimiting = NotificationSetting::getRateLimiting();

        if (!($rateLimiting['enabled'] ?? config('notifier.settings.rate_limiting.enabled', true))) {
            return [
                Stat::make(__('notifier::notifier.widgets.rate_limiting.disabled'), 'Disabled')
                    ->description(__('notifier::notifier.widgets.rate_limiting.disabled_desc'))
                    ->color('gray'),
            ];
        }

        $rateLimitingService = app(RateLimitingService::class);
        $status = $rateLimitingService->getStatus();

        $minuteUsage = $status['limits']['minute']['current'];
        $minuteMax = $status['limits']['minute']['max'];
        $minutePercent = $minuteMax > 0 ? round(($minuteUsage / $minuteMax) * 100, 1) : 0;

        $hourUsage = $status['limits']['hour']['current'];
        $hourMax = $status['limits']['hour']['max'];
        $hourPercent = $hourMax > 0 ? round(($hourUsage / $hourMax) * 100, 1) : 0;

        $dayUsage = $status['limits']['day']['current'];
        $dayMax = $status['limits']['day']['max'];
        $dayPercent = $dayMax > 0 ? round(($dayUsage / $dayMax) * 100, 1) : 0;

        return [
            Stat::make(__('notifier::notifier.widgets.rate_limiting.per_minute'), $minuteUsage . ' / ' . $minuteMax)
                ->description($minutePercent . __('notifier::notifier.widgets.rate_limiting.used'))
                ->descriptionIcon($minutePercent > 80 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($this->getColorForPercent($minutePercent))
                ->chart([$minuteUsage, $minuteMax]),

            Stat::make(__('notifier::notifier.widgets.rate_limiting.per_hour'), $hourUsage . ' / ' . $hourMax)
                ->description($hourPercent . __('notifier::notifier.widgets.rate_limiting.used'))
                ->descriptionIcon($hourPercent > 80 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($this->getColorForPercent($hourPercent)),

            Stat::make(__('notifier::notifier.widgets.rate_limiting.per_day'), $dayUsage . ' / ' . $dayMax)
                ->description($dayPercent . __('notifier::notifier.widgets.rate_limiting.used'))
                ->descriptionIcon($dayPercent > 80 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($this->getColorForPercent($dayPercent)),
        ];
    }

    private function getColorForPercent(float $percent): string
    {
        if ($percent >= 90) {
            return 'danger';
        } elseif ($percent >= 75) {
            return 'warning';
        } else {
            return 'success';
        }
    }
}


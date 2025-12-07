<?php

namespace Usamamuneerchaudhary\Notifier;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Usamamuneerchaudhary\Notifier\Commands\NotifierInstallCommand;
use Usamamuneerchaudhary\Notifier\Commands\SendTestNotificationCommand;
use Usamamuneerchaudhary\Notifier\Http\Controllers\NotificationPreferenceController;
use Usamamuneerchaudhary\Notifier\Models\Notification;
use Usamamuneerchaudhary\Notifier\Models\NotificationChannel;
use Usamamuneerchaudhary\Notifier\Models\NotificationEvent;
use Usamamuneerchaudhary\Notifier\Models\NotificationPreference;
use Usamamuneerchaudhary\Notifier\Models\NotificationTemplate;
use Usamamuneerchaudhary\Notifier\Services\NotifierManager;

class NotifierServiceProvider extends PackageServiceProvider
{
    public static string $name = 'notifier';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasConfigFile()
            ->hasViews()
            ->hasMigrations()
            ->hasCommands([
                NotifierInstallCommand::class,
                SendTestNotificationCommand::class,
            ]);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton('notifier', function () {
            return new NotifierManager();
        });

        $this->app->bind('notifier.channel', NotificationChannel::class);
        $this->app->bind('notifier.event', NotificationEvent::class);
        $this->app->bind('notifier.template', NotificationTemplate::class);
        $this->app->bind('notifier.preference', NotificationPreference::class);
        $this->app->bind('notifier.notification', Notification::class);
    }

    public function packageBooted(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'notifier');

        $this->registerChannelsFromDatabase();
        $this->registerApiRoutes();
    }

    /**
     * Register API routes for user preferences
     */
    protected function registerApiRoutes(): void
    {
        Route::middleware(['api', 'auth'])->prefix('api/notifier/preferences')->group(function () {
            Route::get('/', [NotificationPreferenceController::class, 'index']);
            Route::get('/available', [NotificationPreferenceController::class, 'available']);
            Route::get('/{eventKey}', [NotificationPreferenceController::class, 'show']);
            Route::put('/{eventKey}', [NotificationPreferenceController::class, 'update']);
        });
    }

    protected function registerChannelsFromDatabase(): void
    {
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('notifier_channels')) {
                return;
            }

            $notifier = $this->app->make('notifier');
            $channels = \Usamamuneerchaudhary\Notifier\Models\NotificationChannel::where('is_active', true)->get();

            foreach ($channels as $channel) {
                $driver = $this->getDriverForChannel($channel->type);
                if ($driver) {
                    $notifier->registerChannel($channel->type, $driver);
                }
            }
        } catch (\Exception $e) {
            Log::info($e->getMessage());
            // Silently fail here.
        }
    }

    protected function getDriverForChannel(string $channelType): Services\ChannelDrivers\EmailDriver|Services\ChannelDrivers\PushDriver|Services\ChannelDrivers\DiscordDriver|Services\ChannelDrivers\SlackDriver|Services\ChannelDrivers\SmsDriver|null
    {
        return match ($channelType) {
            'email' => new \Usamamuneerchaudhary\Notifier\Services\ChannelDrivers\EmailDriver(),
            'slack' => new \Usamamuneerchaudhary\Notifier\Services\ChannelDrivers\SlackDriver(),
            'sms' => new \Usamamuneerchaudhary\Notifier\Services\ChannelDrivers\SmsDriver(),
            'push' => new \Usamamuneerchaudhary\Notifier\Services\ChannelDrivers\PushDriver(),
            'discord' => new \Usamamuneerchaudhary\Notifier\Services\ChannelDrivers\DiscordDriver(),
            default => null,
        };
    }
}

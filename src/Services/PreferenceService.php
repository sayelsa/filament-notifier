<?php

namespace Usamamuneerchaudhary\Notifier\Services;

use Illuminate\Support\Facades\Facade;
use Usamamuneerchaudhary\Notifier\Models\NotificationChannel;
use Usamamuneerchaudhary\Notifier\Models\NotificationEvent;
use Usamamuneerchaudhary\Notifier\Models\NotificationPreference;
use Usamamuneerchaudhary\Notifier\Models\NotificationSetting;

class PreferenceService extends Facade
{

    /**
     * facade accessor
     */
    protected static function getFacadeAccessor(): string
    {
        return PreferenceService::class;
    }
    /**
     * Get user preferences for a specific event
     */
    public static function getUserPreferences($user, string $eventKey): array
    {
        $event = NotificationEvent::where('key', $eventKey)->first();

        if (!$event) {
            return [];
        }

        $preference = NotificationPreference::where('user_id', $user->id)
            ->where('notification_event_id', $event->id)
            ->first();

        if ($preference && isset($preference->channels)) {
            return $preference->channels;
        }

        return self::getDefaultPreferences($event);
    }

    /**
     * Get channels configuration for an event ,includes all active channels
     */
    public static function getChannelsForEvent(NotificationEvent $event, ?NotificationPreference $preference): array
    {
        if ($preference && isset($preference->channels)) {
            $channels = $preference->channels;
        } else {
            $channels = self::getDefaultPreferences($event);
        }

        $activeChannels = NotificationChannel::where('is_active', true)
            ->pluck('type')
            ->toArray();

        foreach ($activeChannels as $channelType) {
            if (!isset($channels[$channelType])) {
                $channels[$channelType] = false;
            }
        }

        return $channels;
    }

    /**
     * Get default preferences for an event based on settings
     */
    protected static function getDefaultPreferences(NotificationEvent $event): array
    {
        $defaultChannels = NotificationSetting::get(
            'preferences.default_channels',
            config('notifier.settings.preferences.default_channels', ['email'])
        );

        $preferences = [];
        foreach ($defaultChannels as $channel) {
            $preferences[$channel] = true;
        }

        if (isset($event->settings['channels']) && is_array($event->settings['channels'])) {
            foreach ($event->settings['channels'] as $channel) {
                $preferences[$channel] = true;
            }
        }

        return $preferences;
    }

    /**
     * Check if notification should be sent to a specific channel
     */
    public static function shouldSendToChannel(string $channelType, array $preferences): bool
    {
        $channel = NotificationChannel::where('type', $channelType)
            ->where('is_active', true)
            ->first();

        if (!$channel) {
            return false;
        }

        if (isset($preferences[$channelType]) && !$preferences[$channelType]) {
            return false;
        }

        return true;
    }
}



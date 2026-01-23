<?php

namespace Usamamuneerchaudhary\Notifier\Services;

use Usamamuneerchaudhary\Notifier\Models\EventChannelSetting;
use Usamamuneerchaudhary\Notifier\Models\NotificationChannel;
use Usamamuneerchaudhary\Notifier\Models\NotificationPreference;
use Usamamuneerchaudhary\Notifier\Models\NotificationSetting;

class PreferenceService
{
    /**
     * Get user preferences for a specific event
     */
    public function getUserPreferences($user, string $eventKey): array
    {
        // Check if event exists in config
        $eventService = app(EventService::class);
        if (!$eventService->exists($eventKey)) {
            return [];
        }

        // Get user-specific preferences for this event key
        $preference = NotificationPreference::where('user_id', $user->id)
            ->where('event_key', $eventKey)
            ->first();

        if ($preference && isset($preference->channels)) {
            return $preference->channels;
        }

        return $this->getDefaultPreferencesForEventKey($eventKey);
    }

    /**
     * Get channels configuration for an event key (includes all active channels)
     */
    public function getChannelsForEventKey(string $eventKey, ?NotificationPreference $preference): array
    {
        if ($preference && isset($preference->channels)) {
            $channels = $preference->channels;
        } else {
            $channels = $this->getDefaultPreferencesForEventKey($eventKey);
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
     * Get default preferences for an event key based on settings
     */
    protected function getDefaultPreferencesForEventKey(string $eventKey): array
    {
        $defaultChannels = NotificationSetting::get(
            'preferences.default_channels',
            config('notifier.settings.preferences.default_channels', ['email'])
        );

        $preferences = [];
        foreach ($defaultChannels as $channel) {
            $preferences[$channel] = true;
        }

        // Get admin-configured channels for this event from EventChannelSetting
        $channelSetting = EventChannelSetting::where('event_key', $eventKey)->first();
        if ($channelSetting && is_array($channelSetting->channels)) {
            foreach ($channelSetting->channels as $channel) {
                $preferences[$channel] = true;
            }
        }

        return $preferences;
    }

    /**
     * Check if notification should be sent to a specific channel
     */
    public function shouldSendToChannel($user, string $channelType, array $preferences): bool
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

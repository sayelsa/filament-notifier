<?php

namespace Usamamuneerchaudhary\Notifier\Services;

use Illuminate\Database\Eloquent\Collection;
use Usamamuneerchaudhary\Notifier\Models\NotificationChannel;

class ChannelService
{
    /**
     * Get all channel types defined in config.
     *
     * @return array
     */
    public function all(): array
    {
        return config('notifier.channels', []);
    }

    /**
     * Get enabled channel types.
     *
     * @return array<string, array>
     */
    public function getEnabledTypes(): array
    {
        return collect($this->all())
            ->filter(fn ($channel) => $channel['enabled'] ?? false)
            ->toArray();
    }

    /**
     * Get enabled channel type keys.
     *
     * @return array<string>
     */
    public function getEnabledTypeKeys(): array
    {
        return array_keys($this->getEnabledTypes());
    }

    /**
     * Check if a channel type is enabled in config.
     *
     * @param string $type
     * @return bool
     */
    public function isTypeEnabled(string $type): bool
    {
        $channels = $this->all();
        return isset($channels[$type]) && ($channels[$type]['enabled'] ?? false);
    }

    /**
     * Get channel type options for dropdowns (only enabled types).
     *
     * @return array<string, string>
     */
    public function getTypeOptions(): array
    {
        return collect($this->getEnabledTypes())
            ->mapWithKeys(fn ($channel, $type) => [$type => $channel['label'] ?? ucfirst($type)])
            ->toArray();
    }

    /**
     * Get active NotificationChannel records filtered by enabled config types.
     *
     * @return Collection
     */
    public function getActiveChannels(): Collection
    {
        $enabledTypes = $this->getEnabledTypeKeys();
        
        if (empty($enabledTypes)) {
            return new Collection();
        }

        return NotificationChannel::where('is_active', true)
            ->whereIn('type', $enabledTypes)
            ->orderBy('title')
            ->get();
    }

    /**
     * Get active channel types (from database, filtered by config).
     *
     * @return array<string>
     */
    public function getActiveChannelTypes(): array
    {
        return $this->getActiveChannels()->pluck('type')->toArray();
    }

    /**
     * Check if a specific channel type is both enabled in config and active in database.
     *
     * @param string $type
     * @return bool
     */
    public function isChannelAvailable(string $type): bool
    {
        if (!$this->isTypeEnabled($type)) {
            return false;
        }

        return NotificationChannel::where('type', $type)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Get all NotificationChannel records (active or inactive) filtered by enabled config types.
     *
     * @return Collection
     */
    public function getAllChannels(): Collection
    {
        $enabledTypes = $this->getEnabledTypeKeys();

        if (empty($enabledTypes)) {
            return new Collection();
        }

        return NotificationChannel::whereIn('type', $enabledTypes)
            ->orderBy('title')
            ->get();
    }

    /**
     * Get a specific channel by type (if enabled in config).
     *
     * @param string $type
     * @return ?NotificationChannel
     */
    public function getChannel(string $type): ?NotificationChannel
    {
        if (!$this->isTypeEnabled($type)) {
            return null;
        }

        return NotificationChannel::where('type', $type)->first();
    }
}

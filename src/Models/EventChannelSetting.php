<?php

namespace Usamamuneerchaudhary\Notifier\Models;

use Illuminate\Database\Eloquent\Model;
use Usamamuneerchaudhary\Notifier\Traits\HasTenant;

class EventChannelSetting extends Model
{
    use HasTenant;

    protected $table = 'notifier_event_channel_settings';

    protected $fillable = [
        'tenant_id',
        'event_key',
        'channels',
    ];

    protected $casts = [
        'channels' => 'array',
    ];

    /**
     * Get the channels for a specific event.
     *
     * @param string $eventKey
     * @return array
     */
    public static function getChannelsForEvent(string $eventKey): array
    {
        $setting = static::where('event_key', $eventKey)->first();

        return $setting?->channels ?? [];
    }

    /**
     * Set the channels for a specific event.
     *
     * @param string $eventKey
     * @param array $channels
     * @return static
     */
    public static function setChannelsForEvent(string $eventKey, array $channels): static
    {
        return static::updateOrCreate(
            ['event_key' => $eventKey],
            ['channels' => $channels]
        );
    }

    /**
     * Get all event settings as a key => channels map.
     *
     * @return array
     */
    public static function getAllSettings(): array
    {
        return static::all()->pluck('channels', 'event_key')->toArray();
    }
}

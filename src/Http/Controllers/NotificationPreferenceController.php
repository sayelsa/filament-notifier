<?php

namespace Usamamuneerchaudhary\Notifier\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Usamamuneerchaudhary\Notifier\Http\Requests\UpdatePreferenceRequest;
use Usamamuneerchaudhary\Notifier\Models\EventChannelSetting;
use Usamamuneerchaudhary\Notifier\Models\NotificationChannel;
use Usamamuneerchaudhary\Notifier\Models\NotificationPreference;
use Usamamuneerchaudhary\Notifier\Models\NotificationSetting;
use Usamamuneerchaudhary\Notifier\Services\ChannelService;
use Usamamuneerchaudhary\Notifier\Services\EventService;
use Usamamuneerchaudhary\Notifier\Services\PreferenceService;

class NotificationPreferenceController extends Controller
{
    public function __construct(
        protected PreferenceService $preferenceService,
        protected EventService $eventService
    ) {}

    /**
     * Get all notification preferences for the authenticated user
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();
        $events = $this->eventService->all();
        $preferences = [];

        foreach ($events as $eventKey => $event) {
            $preference = NotificationPreference::where('user_id', $user->id)
                ->where('event_key', $eventKey)
                ->first();

            $channels = $this->preferenceService->getChannelsForEventKey($eventKey, $preference);

            $preferences[] = [
                'event_key' => $eventKey,
                'event_name' => $event['name'],
                'event_group' => $event['group'],
                'description' => $event['description'] ?? '',
                'channels' => $channels,
            ];
        }

        return response()->json(['data' => $preferences]);
    }

    /**
     * Get available events and channels for building UI
     */
    public function available(): JsonResponse
    {
        $events = collect($this->eventService->all())
            ->map(function ($event, $key) {
                $channelSetting = EventChannelSetting::where('event_key', $key)->first();
                return [
                    'key' => $key,
                    'name' => $event['name'],
                    'group' => $event['group'],
                    'description' => $event['description'] ?? '',
                    'default_channels' => $channelSetting ? $channelSetting->channels : [],
                ];
            })
            ->values();

        $channelService = app(ChannelService::class);
        $channels = $channelService->getActiveChannels()
            ->map(function ($channel) {
                return [
                    'type' => $channel->type,
                    'title' => $channel->title,
                    'icon' => $channel->icon,
                ];
            });

        return response()->json([
            'data' => [
                'events' => $events,
                'channels' => $channels,
            ],
        ]);
    }

    /**
     * Get preferences for a specific event
     */
    public function show(string $eventKey): JsonResponse
    {
        $user = Auth::user();
        $event = $this->eventService->get($eventKey);
        
        if (!$event) {
            abort(404, "Event not found");
        }

        $preference = NotificationPreference::where('user_id', $user->id)
            ->where('event_key', $eventKey)
            ->first();

        $channels = $this->preferenceService->getChannelsForEventKey($eventKey, $preference);

        return response()->json([
            'data' => [
                'event_key' => $eventKey,
                'event_name' => $event['name'],
                'event_group' => $event['group'],
                'description' => $event['description'] ?? '',
                'channels' => $channels,
            ],
        ]);
    }

    /**
     * Update preferences for a specific event
     */
    public function update(UpdatePreferenceRequest $request, string $eventKey): JsonResponse
    {
        $user = Auth::user();
        $event = $this->eventService->get($eventKey);
        
        if (!$event) {
            abort(404, "Event not found");
        }

        $preferences = NotificationSetting::getPreferences();
        if (!($preferences['allow_override'] ?? config('notifier.settings.preferences.allow_override', true))) {
            return response()->json([
                'message' => 'User preference override is disabled by administrator.',
            ], 403);
        }

        $validatedChannels = $request->validated()['channels'];
        $activeChannels = NotificationChannel::where('is_active', true)
            ->pluck('type')
            ->toArray();

        foreach (array_keys($validatedChannels) as $channelType) {
            if (!in_array($channelType, $activeChannels)) {
                return response()->json([
                    'message' => "Channel '{$channelType}' is not available or active.",
                ], 422);
            }
        }

        $preference = NotificationPreference::updateOrCreate(
            [
                'user_id' => $user->id,
                'event_key' => $eventKey,
            ],
            [
                'channels' => $validatedChannels,
                'settings' => $request->input('settings', []),
            ]
        );

        return response()->json([
            'data' => [
                'event_key' => $eventKey,
                'event_name' => $event['name'],
                'channels' => $preference->channels,
            ],
            'message' => 'Preferences updated successfully.',
        ]);
    }

}

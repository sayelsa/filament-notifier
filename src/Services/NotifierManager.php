<?php
namespace Usamamuneerchaudhary\Notifier\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Usamamuneerchaudhary\Notifier\Models\Notification;
use Usamamuneerchaudhary\Notifier\Models\NotificationChannel;
use Usamamuneerchaudhary\Notifier\Models\NotificationEvent;
use Usamamuneerchaudhary\Notifier\Models\NotificationTemplate;
use Usamamuneerchaudhary\Notifier\Models\NotificationPreference;
use Usamamuneerchaudhary\Notifier\Models\NotificationSetting;
use Usamamuneerchaudhary\Notifier\Jobs\SendNotificationJob;
use Usamamuneerchaudhary\Notifier\Services\RateLimitingService;

class NotifierManager
{
    protected array $channels = [];
    protected array $events = [];

    public function registerChannel(string $type, $handler): void
    {
        $this->channels[$type] = $handler;
    }

    public function registerEvent(string $key, array $config): void
    {
        $this->events[$key] = $config;
    }

    public function send($user, string $eventKey, array $data = []): void
    {
        try {
            $eventConfig = $this->getEventConfig($eventKey);
            if (!$eventConfig) {
                Log::warning("Event configuration not found for: {$eventKey}");
                return;
            }

            $preferences = $this->getUserPreferences($user, $eventKey);

            $template = $this->getTemplate($eventConfig['template'] ?? null);
            if (!$template) {
                Log::warning("Template not found for event: {$eventKey}");
                return;
            }

            foreach ($eventConfig['channels'] ?? [] as $channelType) {
                if (!$this->shouldSendToChannel($user, $channelType, $preferences)) {
                    continue;
                }

                $notification = $this->createNotification($user, $template, $channelType, $data, $eventKey);
                if (!$notification) {
                    continue;
                }
            }
        } catch (\Exception $e) {
            Log::error("Failed to send notification for event {$eventKey}: " . $e->getMessage());
        }
    }

    public function sendNow($user, string $eventKey, array $data = []): void
    {
        $this->send($user, $eventKey, $data);
    }

    public function sendToChannel($user, string $eventKey, string $channelType, array $data = []): void
    {
        try {
            $eventConfig = $this->getEventConfig($eventKey);
            if (!$eventConfig) {
                Log::warning("Event configuration not found for: {$eventKey}");
                return;
            }

            $template = $this->getTemplate($eventConfig['template'] ?? null);
            if (!$template) {
                Log::warning("Template not found for event: {$eventKey}");
                return;
            }

            $channel = NotificationChannel::where('type', $channelType)
                ->where('is_active', true)
                ->first();

            if (!$channel) {
                Log::warning("Channel {$channelType} is not available or active");
                return;
            }

            $notification = $this->createNotification($user, $template, $channelType, $data, $eventKey);
            if (!$notification) {
                return;
            }
        } catch (\Exception $e) {
            Log::error("Failed to send notification to channel {$channelType} for event {$eventKey}: " . $e->getMessage());
        }
    }

    public function schedule($user, string $eventKey, \Carbon\Carbon $scheduledAt, array $data = []): void
    {
        $eventConfig = $this->getEventConfig($eventKey);
        $template = $this->getTemplate($eventConfig['template'] ?? null);

        if (!$template) {
            return;
        }

        foreach ($eventConfig['channels'] ?? [] as $channelType) {
            $notification = $this->createNotification($user, $template, $channelType, $data, $eventKey);
            if (!$notification) {
                continue;
            }

            $notification->update(['scheduled_at' => $scheduledAt]);
            Queue::later($scheduledAt, new SendNotificationJob($notification->id));
        }
    }

    protected function getEventConfig(string $eventKey): ?array
    {
        if (isset($this->events[$eventKey])) {
            return $this->events[$eventKey];
        }

        $event = NotificationEvent::where('key', $eventKey)->first();
        if ($event) {
            $template = $event->templates()->first();
            if ($template) {
                $channels = $event->settings['channels'] ?? ['email'];
                return [
                    'channels' => $channels,
                    'template' => $template,
                ];
            }
        }

        return null;
    }

    protected function getTemplate($template): ?NotificationTemplate
    {
        if (!$template) {
            return null;
        }

        if ($template instanceof NotificationTemplate) {
            return $template;
        }

        return NotificationTemplate::where('name', $template)->first();
    }

    protected function getUserPreferences($user, string $eventKey): array
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

        $defaultChannels = NotificationSetting::get('preferences.default_channels', config('notifier.settings.preferences.default_channels', ['email']));
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

    protected function shouldSendToChannel($user, string $channelType, array $preferences): bool
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

    protected function createNotification($user, NotificationTemplate $template, string $channelType, array $data, string $eventKey): ?Notification
    {
        $rateLimitingService = app(RateLimitingService::class);
        if (!$rateLimitingService->canSend()) {
            Log::warning("Notification creation blocked due to rate limit", [
                'user_id' => $user->id ?? null,
                'event_key' => $eventKey,
                'channel' => $channelType,
            ]);
            return null;
        }

        // Generate tracking token for analytics
        $trackingToken = Str::random(32);
        $dataWithUser = array_merge($data, ['user' => $user, 'tracking_token' => $trackingToken]);

        $renderedContent = $this->renderTemplate($template, $dataWithUser, $channelType);

        $notificationData = array_merge($data, ['tracking_token' => $trackingToken]);

        $notification = Notification::create([
            'notification_template_id' => $template->id,
            'user_id' => $user->id,
            'channel' => $channelType,
            'subject' => $renderedContent['subject'] ?? '',
            'content' => $renderedContent['content'] ?? '',
            'data' => $notificationData,
            'status' => 'pending',
        ]);

        \Illuminate\Support\Facades\Cache::put(
            "notifier:tracking_token:{$trackingToken}",
            $notification->id,
            now()->addDays(30)
        );

        $rateLimitingService->increment();

        Queue::push(new SendNotificationJob($notification->id));

        return $notification;
    }

    protected function renderTemplate(NotificationTemplate $template, array $data, string $channelType = 'email'): array
    {
        $subject = $template->subject ?? '';
        $content = $template->content ?? '';

        $allData = array_merge([
            'app_name' => config('app.name', 'Laravel'),
            'app_url' => config('app.url', ''),
        ], $data);

        if (isset($data['user']) && is_object($data['user'])) {
            $user = $data['user'];
            $allData['user_name'] = $user->name ?? '';
            $allData['user_email'] = $user->email ?? '';
            $allData['name'] = $allData['name'] ?? ($user->name ?? '');
            $allData['email'] = $allData['email'] ?? ($user->email ?? '');
        }

        // Replace variables using regex to handle occurrences and edge cases
        // Pattern matches {{variable}}
        $pattern = '/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/';

        $subject = preg_replace_callback($pattern, function ($matches) use ($allData) {
            $varName = $matches[1];
            return $allData[$varName] ?? $matches[0];
        }, $subject);

        $content = preg_replace_callback($pattern, function ($matches) use ($allData) {
            $varName = $matches[1];
            return $allData[$varName] ?? $matches[0];
        }, $content);

        $logUnreplaced = NotificationSetting::get('log_unreplaced_variables', config('notifier.settings.log_unreplaced_variables', false));
        if ($logUnreplaced) {
            preg_match_all($pattern, $subject . $content, $unreplaced);
            if (!empty($unreplaced[1])) {
                $missing = array_unique($unreplaced[1]);
                $missing = array_filter($missing, fn($var) => !isset($allData[$var]));
                if (!empty($missing)) {
                    Log::warning("Unreplaced template variables: " . implode(', ', $missing), [
                        'template_id' => $template->id,
                        'template_name' => $template->name,
                    ]);
                }
            }
        }

        // Apply analytics tracking if enabled and for email channel
        if ($channelType === 'email' && isset($data['tracking_token'])) {
            $analytics = NotificationSetting::getAnalytics();

            if ($analytics['enabled'] ?? config('notifier.settings.analytics.enabled', true)) {
                // Rewrite URLs for click tracking
                if ($analytics['track_clicks'] ?? config('notifier.settings.analytics.track_clicks', true)) {
                    $content = $this->rewriteUrlsForTracking($content, $data['tracking_token']);
                }
            }
        }

        return [
            'subject' => $subject,
            'content' => $content,
        ];
    }

    /**
     * Rewrite URLs in content for click tracking
     */
    protected function rewriteUrlsForTracking(string $content, string $token): string
    {
        $appUrl = rtrim(config('app.url', ''), '/');
        $trackingUrl = "{$appUrl}/notifier/track/click/{$token}";

        $pattern = '/href=["\']([^"\']+)["\']/i';

        return preg_replace_callback($pattern, function ($matches) use ($trackingUrl) {
            $originalUrl = $matches[1];

            if (str_contains($originalUrl, '/notifier/track/') ||
                str_starts_with($originalUrl, 'mailto:') ||
                str_starts_with($originalUrl, 'tel:')) {
                return $matches[0];
            }

            $encodedUrl = urlencode($originalUrl);
            $newUrl = "{$trackingUrl}?url={$encodedUrl}";

            return 'href="' . htmlspecialchars($newUrl, ENT_QUOTES, 'UTF-8') . '"';
        }, $content);
    }

    public function getRegisteredChannels(): array
    {
        return array_keys($this->channels);
    }

    public function getRegisteredEvents(): array
    {
        return array_keys($this->events);
    }
}

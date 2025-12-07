<?php

namespace Usamamuneerchaudhary\Notifier\Services\ChannelDrivers;

use Illuminate\Support\Facades\Mail;
use Usamamuneerchaudhary\Notifier\Models\Notification;
use Usamamuneerchaudhary\Notifier\Models\NotificationSetting;

class EmailDriver implements ChannelDriverInterface
{
    public function send(Notification $notification): bool
    {
        try {
            $channel = \Usamamuneerchaudhary\Notifier\Models\NotificationChannel::where('type', 'email')->first();
            $user = $notification->user;

            if (!$user || !$user->email) {
                return false;
            }

            $settings = $channel->settings ?? [];
            $fromAddress = $settings['from_address'] ?? config('mail.from.address', 'noreply@example.com');
            $fromName = $settings['from_name'] ?? config('mail.from.name', 'Notification');

            // Inject tracking pixel if analytics is enabled
            $content = $notification->content;
            $trackingToken = $notification->data['tracking_token'] ?? null;

            if ($trackingToken) {
                $analytics = NotificationSetting::getAnalytics();

                if (($analytics['enabled'] ?? config('notifier.settings.analytics.enabled', true)) &&
                    ($analytics['track_opens'] ?? config('notifier.settings.analytics.track_opens', true))) {
                    $appUrl = rtrim(config('app.url', ''), '/');
                    $trackingPixelUrl = "{$appUrl}/notifier/track/open/{$trackingToken}";
                    $trackingPixel = '<img src="' . htmlspecialchars($trackingPixelUrl, ENT_QUOTES, 'UTF-8') . '" width="1" height="1" style="display:none;" alt="" />';

                    $content .= $trackingPixel;
                }
            }

            $isHtml = strip_tags($content) !== $content;

            if ($isHtml) {
                Mail::html($content, function (\Illuminate\Mail\Message $message) use ($notification, $user, $fromAddress, $fromName) {
                    $message->to($user->email)
                            ->subject($notification->subject)
                            ->from($fromAddress, $fromName);
                });
            } else {
                Mail::raw($content, function (\Illuminate\Mail\Message $message) use ($notification, $user, $fromAddress, $fromName) {
                    $message->to($user->email)
                            ->subject($notification->subject)
                            ->from($fromAddress, $fromName);
                });
            }

            return true;
        } catch (\Exception $e) {
            \Log::error("Email notification failed: " . $e->getMessage());
            return false;
        }
    }

    public function validateSettings(array $settings): bool
    {
        return !empty($settings['from_address'] ?? null);
    }
}

<?php

namespace Usamamuneerchaudhary\Notifier\Services\ChannelDrivers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Usamamuneerchaudhary\Notifier\Models\Notification;
use Usamamuneerchaudhary\Notifier\Services\AnalyticsService;

class EmailDriver implements ChannelDriverInterface
{
    public function send(Notification $notification): bool
    {
        try {
            $channel = app(\Usamamuneerchaudhary\Notifier\Services\ChannelService::class)->getChannel('email');
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
                $analyticsService = app(AnalyticsService::class);
                if ($analyticsService->isOpenTrackingEnabled()) {
                    $content .= $analyticsService->generateTrackingPixel($trackingToken);
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
            Log::error("Email notification failed: " . $e->getMessage());
            return false;
        }
    }

    public function validateSettings(array $settings): bool
    {
        return !empty($settings['from_address'] ?? null);
    }
}

<?php

namespace Usamamuneerchaudhary\Notifier\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Usamamuneerchaudhary\Notifier\Models\Notification;
use Usamamuneerchaudhary\Notifier\Models\NotificationSetting;

class NotificationTrackingController extends Controller
{
    /**
     * Track email open - returns 1x1 transparent pixel
     */
    public function trackOpen(string $token): Response
    {
        try {
            $notification = $this->getNotificationByToken($token);

            if (!$notification) {
                return $this->transparentPixel();
            }

            $analytics = NotificationSetting::getAnalytics();

            if (!($analytics['enabled'] ?? config('notifier.settings.analytics.enabled', true))) {
                return $this->transparentPixel();
            }

            if (!($analytics['track_opens'] ?? config('notifier.settings.analytics.track_opens', true))) {
                return $this->transparentPixel();
            }

            $notification->increment('opens_count');

            if (!$notification->opened_at) {
                $notification->update(['opened_at' => now()]);
            }

        } catch (\Exception $e) {
            Log::error("Failed to track notification open: " . $e->getMessage(), [
                'token' => $token,
            ]);
        }

        return $this->transparentPixel();
    }

    /**
     * Track link click - redirects to original URL
     */
    public function trackClick(string $token, Request $request)
    {
        try {
            $notification = $this->getNotificationByToken($token);

            if (!$notification) {
                return redirect()->to($request->get('url', '/'));
            }

            $analytics = NotificationSetting::getAnalytics();

            if (!($analytics['enabled'] ?? config('notifier.settings.analytics.enabled', true))) {
                return $this->redirectToUrl($request->get('url', '/'));
            }

            if (!($analytics['track_clicks'] ?? config('notifier.settings.analytics.track_clicks', true))) {
                return $this->redirectToUrl($request->get('url', '/'));
            }

            $originalUrl = $request->get('url', '/');

            if (empty($originalUrl) || $originalUrl === '/') {
                return redirect()->to('/');
            }

            $notification->increment('clicks_count');

            if (!$notification->clicked_at) {
                $notification->update(['clicked_at' => now()]);
            }

            return $this->redirectToUrl($originalUrl);

        } catch (\Exception $e) {
            Log::error("Failed to track notification click: " . $e->getMessage(), [
                'token' => $token,
                'url' => $request->get('url'),
            ]);

            return redirect()->to($request->get('url', '/'));
        }
    }

    /**
     * Get notification by token from cache or database
     */
    protected function getNotificationByToken(string $token): ?Notification
    {

        $notificationId = Cache::get("notifier:tracking_token:{$token}");

        if ($notificationId) {
            return Notification::find($notificationId);
        }

        $notifications = Notification::whereJsonContains('data->tracking_token', $token)->get();

        if ($notifications->count() > 0) {
            $notification = $notifications->first();
            Cache::put("notifier:tracking_token:{$token}", $notification->id, now()->addDays(30));
            return $notification;
        }

        return null;
    }

    /**
     * Return 1x1 transparent PNG pixel
     */
    protected function transparentPixel(): Response
    {
        $pixel = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');

        return response($pixel, 200)
            ->header('Content-Type', 'image/png')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    /**
     * redirect to URL
     */
    protected function redirectToUrl(string $url)
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            if (!str_starts_with($url, '/')) {
                $url = '/' . $url;
            }
            return redirect()->to($url);
        }

        $parsedUrl = parse_url($url);
        if (isset($parsedUrl['scheme']) && !in_array($parsedUrl['scheme'], ['http', 'https'])) {
            return redirect()->to('/');
        }

        return redirect()->away($url);
    }
}


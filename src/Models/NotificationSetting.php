<?php


namespace Usamamuneerchaudhary\Notifier\Models;

use Illuminate\Database\Eloquent\Model;
use Usamamuneerchaudhary\Notifier\Services\TenantService;
use Usamamuneerchaudhary\Notifier\Traits\HasTenant;

class NotificationSetting extends Model
{
    use HasTenant;

    protected $table = 'notifier_settings';
    protected $fillable = [
        'tenant_id',
        'key',
        'value',
        'group'
    ];

    protected $casts = [
        'value' => 'json'
    ];

    public static function get(string $key, $default = null)
    {
        $query = static::where('key', $key);

        // Apply tenant filtering if enabled
        $tenantService = app(TenantService::class);
        if ($tenantService->isEnabled()) {
            $tenantId = $tenantService->getCurrentTenantId();
            if ($tenantId !== null) {
                $query->where($tenantService->getTenantColumn(), $tenantId);
            }
        }

        $setting = $query->first();
        return $setting ? $setting->value : $default;
    }

    public static function set(string $key, $value, string $group = 'general'): void
    {
        $data = [
            'value' => $value,
            'group' => $group
        ];

        $whereConditions = ['key' => $key];

        // Apply tenant filtering if enabled
        $tenantService = app(TenantService::class);
        if ($tenantService->isEnabled()) {
            $tenantId = $tenantService->getCurrentTenantId();
            if ($tenantId !== null) {
                $whereConditions[$tenantService->getTenantColumn()] = $tenantId;
                $data[$tenantService->getTenantColumn()] = $tenantId;
            }
        }

        static::updateOrCreate($whereConditions, $data);
    }

    /**
     * Get all preference settings
     */
    public static function getPreferences(): array
    {
        $preferences = static::get('preferences', config('notifier.settings.preferences', []));
        return is_array($preferences) ? $preferences : [];
    }

    /**
     * Get all analytics settings
     */
    public static function getAnalytics(): array
    {
        $analytics = static::get('analytics', config('notifier.settings.analytics', []));
        return is_array($analytics) ? $analytics : [];
    }

    /**
     * Get all rate limiting settings
     */
    public static function getRateLimiting(): array
    {
        $rateLimiting = static::get('rate_limiting', config('notifier.settings.rate_limiting', []));
        return is_array($rateLimiting) ? $rateLimiting : [];
    }
}


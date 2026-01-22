<?php

namespace Usamamuneerchaudhary\Notifier\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool isEnabled()
 * @method static string|null getTenantModel()
 * @method static string getTenantColumn()
 * @method static \Illuminate\Database\Eloquent\Model|null getCurrentTenant()
 * @method static int|string|null getCurrentTenantId()
 * @method static void setTenant(\Illuminate\Database\Eloquent\Model|null $tenant)
 * @method static void clearTenant()
 * @method static mixed withTenant(\Illuminate\Database\Eloquent\Model $tenant, callable $callback)
 * @method static mixed withoutTenant(callable $callback)
 *
 * @see \Usamamuneerchaudhary\Notifier\Services\TenantService
 */
class Tenant extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Usamamuneerchaudhary\Notifier\Services\TenantService::class;
    }
}

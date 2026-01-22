<?php

namespace Usamamuneerchaudhary\Notifier\Services\TenantResolvers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;
use Usamamuneerchaudhary\Notifier\Contracts\TenantResolverInterface;

class SessionTenantResolver implements TenantResolverInterface
{
    protected const SESSION_KEY = 'notifier_tenant_id';

    /**
     * Resolve the current tenant from session.
     *
     * @return Model|null
     */
    public function resolve(): ?Model
    {
        $tenantId = Session::get(self::SESSION_KEY);

        if (!$tenantId) {
            return null;
        }

        $tenantModel = config('notifier.multitenancy.tenant_model');

        if (!$tenantModel || !class_exists($tenantModel)) {
            return null;
        }

        return $tenantModel::find($tenantId);
    }

    /**
     * Get the tenant ID from session.
     *
     * @return int|string|null
     */
    public function getTenantId(): int|string|null
    {
        return Session::get(self::SESSION_KEY);
    }

    /**
     * Set the current tenant in session.
     *
     * @param int|string $tenantId
     * @return void
     */
    public static function setTenant(int|string $tenantId): void
    {
        Session::put(self::SESSION_KEY, $tenantId);
    }

    /**
     * Clear the current tenant from session.
     *
     * @return void
     */
    public static function clearTenant(): void
    {
        Session::forget(self::SESSION_KEY);
    }
}

<?php

namespace Usamamuneerchaudhary\Notifier\Contracts;

use Illuminate\Database\Eloquent\Model;

interface TenantResolverInterface
{
    /**
     * Resolve the current tenant.
     *
     * @return Model|null
     */
    public function resolve(): ?Model;

    /**
     * Get the tenant ID from the resolved tenant.
     *
     * @return int|string|null
     */
    public function getTenantId(): int|string|null;
}

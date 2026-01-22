<?php

namespace Usamamuneerchaudhary\Notifier\Services\TenantResolvers;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Usamamuneerchaudhary\Notifier\Contracts\TenantResolverInterface;

class FilamentTenantResolver implements TenantResolverInterface
{
    /**
     * Resolve the current tenant using Filament.
     *
     * @return Model|null
     */
    public function resolve(): ?Model
    {
        if (!class_exists(Filament::class)) {
            return null;
        }

        return Filament::getTenant();
    }

    /**
     * Get the tenant ID from the resolved tenant.
     *
     * @return int|string|null
     */
    public function getTenantId(): int|string|null
    {
        $tenant = $this->resolve();

        return $tenant?->getKey();
    }
}

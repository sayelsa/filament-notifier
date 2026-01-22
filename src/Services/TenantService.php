<?php

namespace Usamamuneerchaudhary\Notifier\Services;

use Illuminate\Database\Eloquent\Model;
use Usamamuneerchaudhary\Notifier\Contracts\TenantResolverInterface;
use Usamamuneerchaudhary\Notifier\Services\TenantResolvers\FilamentTenantResolver;
use Usamamuneerchaudhary\Notifier\Services\TenantResolvers\SessionTenantResolver;

class TenantService
{
    protected ?TenantResolverInterface $resolver = null;
    protected ?Model $overriddenTenant = null;

    /**
     * Check if multi-tenancy is enabled.
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return config('notifier.multitenancy.enabled', false);
    }

    /**
     * Get the tenant model class.
     *
     * @return string|null
     */
    public function getTenantModel(): ?string
    {
        return config('notifier.multitenancy.tenant_model');
    }

    /**
     * Get the tenant column name.
     *
     * @return string
     */
    public function getTenantColumn(): string
    {
        return config('notifier.multitenancy.tenant_column', 'tenant_id');
    }

    /**
     * Get the tenant resolver instance.
     *
     * @return TenantResolverInterface
     */
    public function getResolver(): TenantResolverInterface
    {
        if ($this->resolver) {
            return $this->resolver;
        }

        $resolverConfig = config('notifier.multitenancy.resolver', 'filament');

        $this->resolver = match ($resolverConfig) {
            'filament' => new FilamentTenantResolver(),
            'session' => new SessionTenantResolver(),
            default => $this->resolveCustomResolver($resolverConfig),
        };

        return $this->resolver;
    }

    /**
     * Resolve a custom resolver class.
     *
     * @param string $className
     * @return TenantResolverInterface
     */
    protected function resolveCustomResolver(string $className): TenantResolverInterface
    {
        if (!class_exists($className)) {
            throw new \InvalidArgumentException("Tenant resolver class [{$className}] does not exist.");
        }

        $resolver = new $className();

        if (!$resolver instanceof TenantResolverInterface) {
            throw new \InvalidArgumentException(
                "Tenant resolver [{$className}] must implement TenantResolverInterface."
            );
        }

        return $resolver;
    }

    /**
     * Get the current tenant.
     *
     * @return Model|null
     */
    public function getCurrentTenant(): ?Model
    {
        if (!$this->isEnabled()) {
            return null;
        }

        if ($this->overriddenTenant) {
            return $this->overriddenTenant;
        }

        return $this->getResolver()->resolve();
    }

    /**
     * Get the current tenant ID.
     *
     * @return int|string|null
     */
    public function getCurrentTenantId(): int|string|null
    {
        if (!$this->isEnabled()) {
            return null;
        }

        if ($this->overriddenTenant) {
            return $this->overriddenTenant->getKey();
        }

        return $this->getResolver()->getTenantId();
    }

    /**
     * Override the current tenant (useful for testing or background jobs).
     *
     * @param Model|null $tenant
     * @return void
     */
    public function setTenant(?Model $tenant): void
    {
        $this->overriddenTenant = $tenant;
    }

    /**
     * Clear the tenant override.
     *
     * @return void
     */
    public function clearTenant(): void
    {
        $this->overriddenTenant = null;
    }

    /**
     * Execute a callback with a specific tenant context.
     *
     * @param Model $tenant
     * @param callable $callback
     * @return mixed
     */
    public function withTenant(Model $tenant, callable $callback): mixed
    {
        $previousTenant = $this->overriddenTenant;

        $this->setTenant($tenant);

        try {
            return $callback();
        } finally {
            $this->overriddenTenant = $previousTenant;
        }
    }

    /**
     * Execute a callback without tenant scoping.
     *
     * @param callable $callback
     * @return mixed
     */
    public function withoutTenant(callable $callback): mixed
    {
        $previousTenant = $this->overriddenTenant;
        $wasEnabled = $this->isEnabled();

        // Temporarily disable by setting null override
        $this->overriddenTenant = null;

        try {
            // Store and restore config
            $originalEnabled = config('notifier.multitenancy.enabled');
            config(['notifier.multitenancy.enabled' => false]);

            $result = $callback();

            config(['notifier.multitenancy.enabled' => $originalEnabled]);

            return $result;
        } finally {
            $this->overriddenTenant = $previousTenant;
        }
    }
}

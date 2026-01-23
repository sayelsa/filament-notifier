<?php

namespace Usamamuneerchaudhary\Notifier\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Usamamuneerchaudhary\Notifier\Services\TenantService;

trait HasTenant
{
    /**
     * Boot the HasTenant trait.
     *
     * @return void
     */
    public static function bootHasTenant(): void
    {
        // Add global scope for tenant filtering
        static::addGlobalScope('tenant', function (Builder $builder) {
            $tenantService = app(TenantService::class);

            if (!$tenantService->isEnabled()) {
                return;
            }

            $tenantId = $tenantService->getCurrentTenantId();
            $tenantColumn = $tenantService->getTenantColumn();

            if ($tenantId !== null) {
                $builder->where($builder->getModel()->getTable() . '.' . $tenantColumn, $tenantId);
            }
        });

        // Auto-set tenant_id on creating
        static::creating(function (Model $model) {
            $tenantService = app(TenantService::class);

            if (!$tenantService->isEnabled()) {
                return;
            }

            $tenantColumn = $tenantService->getTenantColumn();
            $tenantId = $tenantService->getCurrentTenantId();

            // Only set if not already set
            if ($tenantId !== null && empty($model->{$tenantColumn})) {
                $model->{$tenantColumn} = $tenantId;
            }
        });
    }

    /**
     * Initialize the HasTenant trait for an instance.
     * This adds dynamic relationships based on the tenant model name.
     *
     * @return void
     */
    public function initializeHasTenant(): void
    {
        // Nothing needed here - we use __call for dynamic relationships
    }

    /**
     * Get the tenant relationship name based on the configured tenant model.
     *
     * @return string|null
     */
    public static function getTenantRelationshipName(): ?string
    {
        $tenantService = app(TenantService::class);
        
        if (!$tenantService->isEnabled()) {
            return null;
        }
        
        // Check for explicit config first
        $configuredName = config('notifier.multitenancy.ownership_relationship');
        if ($configuredName) {
            return $configuredName;
        }
        
        $tenantModel = $tenantService->getTenantModel();
        
        if (!$tenantModel) {
            return null;
        }
        
        // Get the class basename and convert to lowercase (e.g., "Org" -> "org", "Team" -> "team")
        return strtolower(class_basename($tenantModel));
    }

    /**
     * Get the tenant relationship.
     *
     * @return BelongsTo|null
     */
    public function tenant(): ?BelongsTo
    {
        $tenantService = app(TenantService::class);

        if (!$tenantService->isEnabled()) {
            return null;
        }

        $tenantModel = $tenantService->getTenantModel();
        $tenantColumn = $tenantService->getTenantColumn();

        if (!$tenantModel) {
            return null;
        }

        return $this->belongsTo($tenantModel, $tenantColumn);
    }

    /**
     * Handle dynamic method calls for tenant relationship.
     * This allows Filament to call the relationship by the tenant model name (e.g., org(), team()).
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        $tenantRelationshipName = static::getTenantRelationshipName();
        
        // If the method name matches the tenant relationship name, return the tenant relationship
        if ($tenantRelationshipName && $method === $tenantRelationshipName) {
            return $this->tenant();
        }

        // Otherwise, defer to parent
        return parent::__call($method, $parameters);
    }

    /**
     * Scope a query to a specific tenant.
     *
     * @param Builder $query
     * @param Model|int|string $tenant
     * @return Builder
     */
    public function scopeForTenant(Builder $query, Model|int|string $tenant): Builder
    {
        $tenantService = app(TenantService::class);
        $tenantColumn = $tenantService->getTenantColumn();

        $tenantId = $tenant instanceof Model ? $tenant->getKey() : $tenant;

        return $query->withoutGlobalScope('tenant')->where($tenantColumn, $tenantId);
    }

    /**
     * Scope a query to exclude tenant filtering.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeWithoutTenantScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope('tenant');
    }

    /**
     * Scope a query to include all tenants (alias for withoutTenantScope).
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeAllTenants(Builder $query): Builder
    {
        return $query->withoutGlobalScope('tenant');
    }
}


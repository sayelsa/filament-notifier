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

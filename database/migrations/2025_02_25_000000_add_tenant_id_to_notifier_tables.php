<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tenantColumn = config('notifier.multitenancy.tenant_column', 'tenant_id');

        // Add tenant_id to notifier_channels
        if (!Schema::hasColumn('notifier_channels', $tenantColumn)) {
            Schema::table('notifier_channels', function (Blueprint $table) use ($tenantColumn) {
                $table->unsignedBigInteger($tenantColumn)->nullable()->after('id');
                $table->index($tenantColumn);
            });
        }

        // Add tenant_id to notifier_templates
        if (!Schema::hasColumn('notifier_templates', $tenantColumn)) {
            Schema::table('notifier_templates', function (Blueprint $table) use ($tenantColumn) {
                $table->unsignedBigInteger($tenantColumn)->nullable()->after('id');
                $table->index($tenantColumn);
            });
        }

        // Add tenant_id to notifier_preferences
        if (!Schema::hasColumn('notifier_preferences', $tenantColumn)) {
            Schema::table('notifier_preferences', function (Blueprint $table) use ($tenantColumn) {
                $table->unsignedBigInteger($tenantColumn)->nullable()->after('id');
                $table->index($tenantColumn);
            });
        }

        // Add tenant_id to notifier_notifications
        if (!Schema::hasColumn('notifier_notifications', $tenantColumn)) {
            Schema::table('notifier_notifications', function (Blueprint $table) use ($tenantColumn) {
                $table->unsignedBigInteger($tenantColumn)->nullable()->after('id');
                $table->index($tenantColumn);
            });
        }

        // Add tenant_id to notifier_settings
        if (!Schema::hasColumn('notifier_settings', $tenantColumn)) {
            Schema::table('notifier_settings', function (Blueprint $table) use ($tenantColumn) {
                $table->unsignedBigInteger($tenantColumn)->nullable()->after('id');
                $table->index($tenantColumn);
            });

            // Drop the unique constraint on 'key' and recreate with tenant_id
            $this->dropUniqueIfExists('notifier_settings', 'notifier_settings_key_unique');
            
            Schema::table('notifier_settings', function (Blueprint $table) use ($tenantColumn) {
                $table->unique([$tenantColumn, 'key'], 'notifier_settings_tenant_key_unique');
            });
        }
    }

    /**
     * Drop a unique constraint if it exists (cross-database compatible)
     */
    protected function dropUniqueIfExists(string $table, string $indexName): void
    {
        try {
            Schema::table($table, function (Blueprint $table) use ($indexName) {
                $table->dropUnique($indexName);
            });
        } catch (\Exception $e) {
            // Unique constraint doesn't exist, continue silently
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tenantColumn = config('notifier.multitenancy.tenant_column', 'tenant_id');

        // Remove tenant_id from notifier_channels
        if (Schema::hasColumn('notifier_channels', $tenantColumn)) {
            Schema::table('notifier_channels', function (Blueprint $table) use ($tenantColumn) {
                $table->dropIndex([$tenantColumn]);
                $table->dropColumn($tenantColumn);
            });
        }

        // Remove tenant_id from notifier_templates
        if (Schema::hasColumn('notifier_templates', $tenantColumn)) {
            Schema::table('notifier_templates', function (Blueprint $table) use ($tenantColumn) {
                $table->dropIndex([$tenantColumn]);
                $table->dropColumn($tenantColumn);
            });
        }

        // Remove tenant_id from notifier_preferences
        if (Schema::hasColumn('notifier_preferences', $tenantColumn)) {
            Schema::table('notifier_preferences', function (Blueprint $table) use ($tenantColumn) {
                $table->dropIndex([$tenantColumn]);
                $table->dropColumn($tenantColumn);
            });
        }

        // Remove tenant_id from notifier_notifications
        if (Schema::hasColumn('notifier_notifications', $tenantColumn)) {
            Schema::table('notifier_notifications', function (Blueprint $table) use ($tenantColumn) {
                $table->dropIndex([$tenantColumn]);
                $table->dropColumn($tenantColumn);
            });
        }

        // Remove tenant_id from notifier_settings and restore unique constraint
        if (Schema::hasColumn('notifier_settings', $tenantColumn)) {
            $this->dropUniqueIfExists('notifier_settings', 'notifier_settings_tenant_key_unique');
            
            Schema::table('notifier_settings', function (Blueprint $table) {
                $table->unique('key', 'notifier_settings_key_unique');
            });
            
            Schema::table('notifier_settings', function (Blueprint $table) use ($tenantColumn) {
                $table->dropIndex(['tenant_id']);
                $table->dropColumn($tenantColumn);
            });
        }
    }
};

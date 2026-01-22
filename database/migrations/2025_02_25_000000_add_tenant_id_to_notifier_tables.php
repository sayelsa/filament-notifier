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

        // Add tenant_id to notifier_events
        if (!Schema::hasColumn('notifier_events', $tenantColumn)) {
            Schema::table('notifier_events', function (Blueprint $table) use ($tenantColumn) {
                $table->unsignedBigInteger($tenantColumn)->nullable()->after('id');
                $table->index($tenantColumn);
            });

            // Drop the unique constraint on 'key' and recreate with tenant_id
            // This allows same event key across different tenants
            Schema::table('notifier_events', function (Blueprint $table) use ($tenantColumn) {
                $table->dropUnique(['key']);
                $table->unique([$tenantColumn, 'key']);
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
            // This allows same setting key across different tenants
            Schema::table('notifier_settings', function (Blueprint $table) use ($tenantColumn) {
                $table->dropUnique(['key']);
                $table->unique([$tenantColumn, 'key']);
            });
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

        // Remove tenant_id from notifier_events and restore unique constraint
        if (Schema::hasColumn('notifier_events', $tenantColumn)) {
            Schema::table('notifier_events', function (Blueprint $table) use ($tenantColumn) {
                $table->dropUnique([$tenantColumn, 'key']);
                $table->unique('key');
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
            Schema::table('notifier_settings', function (Blueprint $table) use ($tenantColumn) {
                $table->dropUnique([$tenantColumn, 'key']);
                $table->unique('key');
                $table->dropIndex([$tenantColumn]);
                $table->dropColumn($tenantColumn);
            });
        }
    }
};

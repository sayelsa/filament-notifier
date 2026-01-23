<?php

namespace Usamamuneerchaudhary\Notifier\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class NotifierMigrateCommand extends Command
{
    protected $signature = 'notifier:migrate 
                            {--fresh : Drop all notifier tables and re-run migrations}
                            {--seed : Run sample data seeder after migration}';

    protected $description = 'Run the Notifier package migrations';

    public function handle(): int
    {
        if ($this->option('fresh')) {
            if ($this->confirm('This will drop all notifier tables. Are you sure?')) {
                $this->info('Dropping notifier tables...');
                $this->dropNotifierTables();
                
                // Also remove from migrations table so they can run again
                $this->cleanMigrationRecords();
            } else {
                $this->info('Migration cancelled.');
                return self::SUCCESS;
            }
        }

        $this->info('Running Notifier migrations...');
        
        // Run the standard migrate command - package migrations are auto-loaded by the service provider
        $this->call('migrate');

        $this->info('✓ Migrations completed successfully!');

        if ($this->option('seed')) {
            $this->call('notifier:seed');
        }

        return self::SUCCESS;
    }

    protected function dropNotifierTables(): void
    {
        $tables = [
            'notifier_notifications',
            'notifier_preferences',
            'notifier_templates',
            'notifier_events',
            'notifier_channels',
            'notifier_settings',
        ];

        foreach ($tables as $table) {
            Schema::dropIfExists($table);
            $this->line("  Dropped: {$table}");
        }
    }

    protected function cleanMigrationRecords(): void
    {
        if (!Schema::hasTable('migrations')) {
            return;
        }

        $migrations = [
            'create_notification_channels_table',
            'create_notification_events_table',
            'create_notification_templates',
            'create_notification_preferences',
            'create_notifications',
            'add_settings_to_notification_events_table',
            'create_notification_settings_table',
            'add_analytics_to_notifications',
            'add_tenant_id_to_notifier_tables',
        ];

        foreach ($migrations as $migration) {
            \Illuminate\Support\Facades\DB::table('migrations')
                ->where('migration', 'like', "%{$migration}%")
                ->delete();
        }

        $this->line('  Cleaned migration records');
    }
}


<?php

namespace Usamamuneerchaudhary\Notifier\Commands;

use Illuminate\Console\Command;

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
            } else {
                $this->info('Migration cancelled.');
                return self::SUCCESS;
            }
        }

        $this->info('Running Notifier migrations...');
        $this->call('migrate', [
            '--path' => 'vendor/usamamuneerchaudhary/notifier/database/migrations',
            '--force' => true,
        ]);

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
            \Illuminate\Support\Facades\Schema::dropIfExists($table);
            $this->line("  Dropped: {$table}");
        }
    }
}

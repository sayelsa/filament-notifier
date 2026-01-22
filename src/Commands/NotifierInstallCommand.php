<?php

namespace Usamamuneerchaudhary\Notifier\Commands;

use Illuminate\Console\Command;

class NotifierInstallCommand extends Command
{
    protected $signature = 'notifier:install 
                            {--force : Overwrite existing files}
                            {--skip-config : Skip publishing configuration}
                            {--skip-migrate : Skip running migrations}
                            {--skip-seed : Skip creating sample data}';

    protected $description = 'Install the Filament Notifier package (runs publish, migrate, and seed)';

    public function handle(): int
    {
        $this->info('Installing Filament Notifier...');
        $this->newLine();

        // Step 1: Publish assets
        if (!$this->option('skip-config')) {
            $this->call('notifier:publish', [
                '--force' => $this->option('force'),
            ]);
            $this->newLine();
        }

        // Step 2: Run migrations
        if (!$this->option('skip-migrate')) {
            $this->call('notifier:migrate');
            $this->newLine();
        }

        // Step 3: Seed sample data
        if (!$this->option('skip-seed')) {
            if ($this->confirm('Would you like to create sample notification channels and templates?')) {
                $this->call('notifier:seed');
                $this->newLine();
            }
        }

        $this->info('🎉 Filament Notifier has been installed successfully!');
        $this->newLine();
        $this->info('You can now access the notification management panel in your Filament admin.');
        $this->newLine();
        $this->info('Available commands:');
        $this->line('  php artisan notifier:publish   - Publish config/views/migrations');
        $this->line('  php artisan notifier:migrate   - Run migrations');
        $this->line('  php artisan notifier:seed      - Create sample data');

        return self::SUCCESS;
    }
}


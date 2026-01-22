<?php

namespace Usamamuneerchaudhary\Notifier\Commands;

use Illuminate\Console\Command;

class NotifierPublishCommand extends Command
{
    protected $signature = 'notifier:publish 
                            {--config : Publish only the configuration file}
                            {--views : Publish only the view files}
                            {--migrations : Publish only the migration files}
                            {--force : Overwrite existing files}';

    protected $description = 'Publish Notifier package assets (config, views, migrations)';

    public function handle(): int
    {
        $publishConfig = $this->option('config');
        $publishViews = $this->option('views');
        $publishMigrations = $this->option('migrations');
        $force = $this->option('force');

        // If no specific option is provided, publish everything
        $publishAll = !$publishConfig && !$publishViews && !$publishMigrations;

        if ($publishAll || $publishConfig) {
            $this->info('Publishing configuration file...');
            $this->call('vendor:publish', [
                '--provider' => 'Usamamuneerchaudhary\Notifier\NotifierServiceProvider',
                '--tag' => 'notifier-config',
                '--force' => $force,
            ]);
            $this->info('✓ Configuration file published to config/notifier.php');
        }

        if ($publishAll || $publishViews) {
            $this->info('Publishing view files...');
            $this->call('vendor:publish', [
                '--provider' => 'Usamamuneerchaudhary\Notifier\NotifierServiceProvider',
                '--tag' => 'notifier-views',
                '--force' => $force,
            ]);
            $this->info('✓ View files published');
        }

        if ($publishAll || $publishMigrations) {
            $this->info('Publishing migration files...');
            $this->call('vendor:publish', [
                '--provider' => 'Usamamuneerchaudhary\Notifier\NotifierServiceProvider',
                '--tag' => 'notifier-migrations',
                '--force' => $force,
            ]);
            $this->info('✓ Migration files published');
        }

        $this->newLine();
        $this->info('Assets published successfully!');
        $this->info('Next step: Configure your settings in config/notifier.php, then run: php artisan notifier:migrate');

        return self::SUCCESS;
    }
}

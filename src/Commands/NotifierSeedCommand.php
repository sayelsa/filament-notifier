<?php

namespace Usamamuneerchaudhary\Notifier\Commands;

use Illuminate\Console\Command;
use Usamamuneerchaudhary\Notifier\Models\EventChannelSetting;
use Usamamuneerchaudhary\Notifier\Models\NotificationChannel;
use Usamamuneerchaudhary\Notifier\Models\NotificationTemplate;
use Usamamuneerchaudhary\Notifier\Services\EventService;
use Usamamuneerchaudhary\Notifier\Services\TenantService;

class NotifierSeedCommand extends Command
{
    protected $signature = 'notifier:seed 
                            {--channels : Seed only notification channels}
                            {--events : Seed only event channel settings}
                            {--templates : Seed only notification templates}
                            {--tenant= : Tenant ID to associate seeded data with (for multi-tenant setups)}';

    protected $description = 'Create sample notification channels, event channel settings, and templates';

    public function handle(): int
    {
        $seedChannels = $this->option('channels');
        $seedEvents = $this->option('events');
        $seedTemplates = $this->option('templates');
        $tenantId = $this->option('tenant');

        // If no specific option is provided, seed everything
        $seedAll = !$seedChannels && !$seedEvents && !$seedTemplates;

        // Set up tenant context if provided
        $tenantService = app(TenantService::class);
        $tenant = null;

        if ($tenantId && $tenantService->isEnabled()) {
            $tenantModel = $tenantService->getTenantModel();
            if ($tenantModel) {
                $tenant = $tenantModel::find($tenantId);
                if ($tenant) {
                    $tenantService->setTenant($tenant);
                    $this->info("Seeding data for tenant: {$tenant->getKey()}");
                } else {
                    $this->error("Tenant with ID {$tenantId} not found.");
                    return self::FAILURE;
                }
            }
        } elseif ($tenantService->isEnabled() && !$tenantId) {
            $this->warn('Multi-tenancy is enabled but no --tenant specified. Data will be created without a tenant.');
            if (!$this->confirm('Continue?')) {
                return self::SUCCESS;
            }
        }

        if ($seedAll || $seedChannels) {
            $this->seedChannels();
        }

        if ($seedAll || $seedEvents) {
            $this->seedEventChannelSettings();
        }

        if ($seedAll || $seedTemplates) {
            $this->seedTemplates();
        }

        // Clear tenant override
        if ($tenant) {
            $tenantService->clearTenant();
        }

        $this->newLine();
        $this->info('Sample data created successfully!');

        return self::SUCCESS;
    }

    protected function seedChannels(): void
    {
        $this->info('Creating sample channels...');

        $channels = [
            [
                'title' => 'Email Notifications',
                'type' => 'email',
                'icon' => 'heroicon-o-envelope',
                'is_active' => true,
                'settings' => [
                    'smtp_host' => 'smtp.mailtrap.io',
                    'smtp_port' => 2525,
                ],
            ],
            [
                'title' => 'Slack Notifications',
                'type' => 'slack',
                'icon' => 'heroicon-o-chat-bubble-left-right',
                'is_active' => true,
                'settings' => [
                    'webhook_url' => env('SLACK_WEBHOOK_URL', ''),
                ],
            ],
            [
                'title' => 'Database Notifications',
                'type' => 'database',
                'icon' => 'heroicon-o-circle-stack',
                'is_active' => true,
                'settings' => [],
            ],
        ];

        foreach ($channels as $channelData) {
            $channel = NotificationChannel::firstOrCreate(
                ['type' => $channelData['type']],
                $channelData
            );
            $this->line("  ✓ {$channel->title}");
        }
    }

    protected function seedEventChannelSettings(): void
    {
        $this->info('Creating event channel settings from config...');
        $this->info('(Events are now defined in config/notifier.php)');

        $eventService = app(EventService::class);
        $events = $eventService->all();

        if (empty($events)) {
            $this->warn('  No events found in config. Add events to config/notifier.php');
            return;
        }

        // Create channel settings for each configured event
        foreach ($events as $eventKey => $event) {
            $setting = EventChannelSetting::firstOrCreate(
                ['event_key' => $eventKey],
                ['channels' => ['email']] // Default to email
            );
            $this->line("  ✓ {$event['name']} ({$eventKey})");
        }
    }

    protected function seedTemplates(): void
    {
        $this->info('Creating sample templates...');

        $templates = [
            [
                'name' => 'Welcome Email',
                'event_key' => 'user.registered',
                'subject' => 'Welcome to {{app_name}}, {{name}}!',
                'content' => "Hi {{name}},\n\nWelcome to {{app_name}}! We're excited to have you on board.\n\nBest regards,\nThe {{app_name}} Team",
                'variables' => [
                    'name' => 'User\'s full name',
                    'app_name' => 'Application name',
                ],
            ],
            [
                'name' => 'Password Reset',
                'event_key' => 'user.password_reset',
                'subject' => 'Reset Your Password',
                'content' => "Hi {{name}},\n\nYou requested a password reset. Click the link below to reset your password:\n\n{{reset_link}}\n\nIf you didn't request this, please ignore this email.\n\nBest regards,\nThe {{app_name}} Team",
                'variables' => [
                    'name' => 'User\'s full name',
                    'reset_link' => 'Password reset link',
                    'app_name' => 'Application name',
                ],
            ],
        ];

        foreach ($templates as $templateData) {
            $template = NotificationTemplate::firstOrCreate(
                ['name' => $templateData['name'], 'event_key' => $templateData['event_key']],
                $templateData
            );
            $this->line("  ✓ {$template->name}");
        }
    }
}

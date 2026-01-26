<?php

namespace Usamamuneerchaudhary\Notifier\Filament\Pages;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Usamamuneerchaudhary\Notifier\Filament\Widgets\NotificationStatsOverview;
use Usamamuneerchaudhary\Notifier\Models\NotificationSetting;
use Usamamuneerchaudhary\Notifier\Models\NotificationChannel; // Keep for type hint
use Usamamuneerchaudhary\Notifier\Services\ChannelService;

class NotificationSettings extends Page
{
    use InteractsWithForms;

    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static string|null|\UnitEnum $navigationGroup = 'Notifier';
    protected static ?int $navigationSort = 1;
    protected string $view = 'notifier::pages.settings';

    public static function getNavigationLabel(): string
    {
        return __('notifier::notifier.pages.settings.navigation_label');
    }

    public function getTitle(): string
    {
        return __('notifier::notifier.pages.settings.title');
    }

    public array $data;

    public function mount(): void
    {
        $channels = app(ChannelService::class)->getAllChannels();
        $channelsData = [];

        foreach ($channels as $channel) {
            $settings = $channel->settings ?? [];
            $channelsData[$channel->type] = array_merge([
                'enabled' => $channel->is_active,
            ], $settings);
        }

        // Load preferences settings
        $preferences = NotificationSetting::get('preferences', config('notifier.settings.preferences', []));
        // Load analytics settings
        $analytics = NotificationSetting::get('analytics', config('notifier.settings.analytics', []));
        // Load rate limiting settings
        $rateLimiting = NotificationSetting::get('rate_limiting', config('notifier.settings.rate_limiting', []));

        $this->form->fill([
            'enabled' => NotificationSetting::get('enabled', true),
            'queue_name' => NotificationSetting::get('queue_name', 'default'),
            'default_channel' => NotificationSetting::get('default_channel', 'email'),
            'channels' => $channelsData,
            'preferences' => [
                'enabled' => $preferences['enabled'] ?? true,
                'default_channels' => $preferences['default_channels'] ?? ['email'],
                'allow_override' => $preferences['allow_override'] ?? true,
            ],
            'analytics' => [
                'enabled' => $analytics['enabled'] ?? true,
                'track_opens' => $analytics['track_opens'] ?? true,
                'track_clicks' => $analytics['track_clicks'] ?? true,
                'retention_days' => $analytics['retention_days'] ?? 90,
            ],
            'rate_limiting' => [
                'enabled' => $rateLimiting['enabled'] ?? true,
                'max_per_minute' => $rateLimiting['max_per_minute'] ?? 60,
                'max_per_hour' => $rateLimiting['max_per_hour'] ?? 1000,
                'max_per_day' => $rateLimiting['max_per_day'] ?? 10000,
            ],
        ]);
    }

    public function form(Schema $schema): Schema
    {
        $channelService = app(ChannelService::class);
        $channels = $channelService->getAllChannels();
        $tabs = [
            Tab::make(__('notifier::notifier.pages.settings.tabs.general'))
                ->icon('heroicon-o-cog')
                ->schema([
                    Toggle::make('enabled')
                        ->label(__('notifier::notifier.pages.settings.fields.enable_notifications'))
                        ->default(true),
                    TextInput::make('queue_name')
                        ->label(__('notifier::notifier.pages.settings.fields.queue_name'))
                        ->default('default')
                        ->required(),
                    Select::make('default_channel')
                        ->label(__('notifier::notifier.pages.settings.fields.default_channel'))
                        ->options($channelService->getAllChannels()->pluck('title', 'type')->toArray())
                        ->required(),
                ]),
            Tab::make(__('notifier::notifier.pages.settings.tabs.preferences'))
                ->icon('heroicon-o-user-circle')
                ->schema([
                    Section::make(__('notifier::notifier.pages.settings.sections.user_preferences.heading'))
                        ->description(__('notifier::notifier.pages.settings.sections.user_preferences.description'))
                        ->schema([
                            Toggle::make('preferences.enabled')
                                ->label(__('notifier::notifier.pages.settings.preferences.enable'))
                                ->default(true),
                            Select::make('preferences.default_channels')
                                ->label(__('notifier::notifier.pages.settings.preferences.default_channels'))
                                ->multiple()
                                ->options($channelService->getAllChannels()->pluck('title', 'type')->toArray())
                                ->default(['email'])
                                ->required(),
                            Toggle::make('preferences.allow_override')
                                ->label(__('notifier::notifier.pages.settings.preferences.allow_override.label'))
                                ->default(true)
                                ->helperText(__('notifier::notifier.pages.settings.preferences.allow_override.helper_text')),
                        ]),
                ]),
            Tab::make(__('notifier::notifier.pages.settings.tabs.analytics'))
                ->icon('heroicon-o-chart-bar')
                ->schema([
                    Section::make(__('notifier::notifier.pages.settings.sections.analytics.heading'))
                        ->description(__('notifier::notifier.pages.settings.sections.analytics.description'))
                        ->schema([
                            Toggle::make('analytics.enabled')
                                ->label(__('notifier::notifier.pages.settings.analytics.enable'))
                                ->default(true),
                            Toggle::make('analytics.track_opens')
                                ->label(__('notifier::notifier.pages.settings.analytics.track_opens'))
                                ->default(true)
                                ->visible(fn(Get $get): bool => $get('analytics.enabled')),
                            Toggle::make('analytics.track_clicks')
                                ->label(__('notifier::notifier.pages.settings.analytics.track_clicks'))
                                ->default(true)
                                ->visible(fn(Get $get): bool => $get('analytics.enabled')),
                            TextInput::make('analytics.retention_days')
                                ->label(__('notifier::notifier.pages.settings.analytics.retention_days'))
                                ->numeric()
                                ->default(90)
                                ->required()
                                ->visible(fn(Get $get): bool => $get('analytics.enabled')),
                        ]),
                ]),
            Tab::make(__('notifier::notifier.pages.settings.tabs.rate_limiting'))
                ->icon('heroicon-o-clock')
                ->schema([
                    Section::make(__('notifier::notifier.pages.settings.sections.rate_limiting.heading'))
                        ->description(__('notifier::notifier.pages.settings.sections.rate_limiting.description'))
                        ->schema([
                            Toggle::make('rate_limiting.enabled')
                                ->label(__('notifier::notifier.pages.settings.rate_limiting.enable'))
                                ->default(true),
                            TextInput::make('rate_limiting.max_per_minute')
                                ->label(__('notifier::notifier.pages.settings.rate_limiting.max_per_minute'))
                                ->numeric()
                                ->default(60)
                                ->required()
                                ->visible(fn(Get $get): bool => $get('rate_limiting.enabled')),
                            TextInput::make('rate_limiting.max_per_hour')
                                ->label(__('notifier::notifier.pages.settings.rate_limiting.max_per_hour'))
                                ->numeric()
                                ->default(1000)
                                ->required()
                                ->visible(fn(Get $get): bool => $get('rate_limiting.enabled')),
                            TextInput::make('rate_limiting.max_per_day')
                                ->label(__('notifier::notifier.pages.settings.rate_limiting.max_per_day'))
                                ->numeric()
                                ->default(10000)
                                ->required()
                                ->visible(fn(Get $get): bool => $get('rate_limiting.enabled')),
                        ]),
                ]),
        ];

        // Dynamically create tabs for each channel
        foreach ($channels as $channel) {
            $tabs[] = Tab::make($channel->title)
                ->icon($channel->icon ?? 'heroicon-o-bell')
                ->schema(self::getChannelSchema($channel));
        }

        return $schema
            ->schema([
                Tabs::make('Settings')
                    ->tabs($tabs)
                    ->columnSpanFull()
            ])
            ->statePath('data');
    }

    protected static function getChannelSchema(NotificationChannel $channel): array
    {
        $schema = [
            Toggle::make("channels.{$channel->type}.enabled")
                ->label(__('notifier::notifier.pages.settings.channels.enable', ['channel' => $channel->title])),
        ];

        $fields = match ($channel->type) {
            'email' => [
                TextInput::make("channels.{$channel->type}.from_address")
                    ->label(__('notifier::notifier.pages.settings.channels.from_address'))
                    ->email()
                    ->required()
                    ->visible(fn(Get $get): bool => $get("channels.{$channel->type}.enabled")),
                TextInput::make("channels.{$channel->type}.from_name")
                    ->label(__('notifier::notifier.pages.settings.channels.from_name'))
                    ->required()
                    ->visible(fn(Get $get): bool => $get("channels.{$channel->type}.enabled")),
            ],
            'slack' => [
                TextInput::make("channels.{$channel->type}.webhook_url")
                    ->label(__('notifier::notifier.pages.settings.channels.webhook_url'))
                    ->url()
                    ->required()
                    ->visible(fn(Get $get): bool => $get("channels.{$channel->type}.enabled")),
                TextInput::make("channels.{$channel->type}.channel")
                    ->label(__('notifier::notifier.pages.settings.channels.channel'))
                    ->default('#notifications')
                    ->visible(fn(Get $get): bool => $get("channels.{$channel->type}.enabled")),
                TextInput::make("channels.{$channel->type}.username")
                    ->label(__('notifier::notifier.pages.settings.channels.username'))
                    ->default('Notification Bot')
                    ->visible(fn(Get $get): bool => $get("channels.{$channel->type}.enabled")),
            ],
            'sms' => [
                Select::make("channels.{$channel->type}.provider")
                    ->label(__('notifier::notifier.pages.settings.channels.provider'))
                    ->options([
                        'twilio' => 'Twilio',
                        'vonage' => 'Vonage (Nexmo)',
                        'generic' => 'Generic API',
                    ])
                    ->required()
                    ->visible(fn(Get $get): bool => $get("channels.{$channel->type}.enabled")),
                TextInput::make("channels.{$channel->type}.twilio_account_sid")
                    ->label(__('notifier::notifier.pages.settings.channels.twilio_account_sid'))
                    ->visible(fn(Get $get): bool => $get("channels.{$channel->type}.enabled") && $get("channels.{$channel->type}.provider") === 'twilio'),
                TextInput::make("channels.{$channel->type}.twilio_auth_token")
                    ->label(__('notifier::notifier.pages.settings.channels.twilio_auth_token'))
                    ->password()
                    ->visible(fn(Get $get): bool => $get("channels.{$channel->type}.enabled") && $get("channels.{$channel->type}.provider") === 'twilio'),
                TextInput::make("channels.{$channel->type}.twilio_phone_number")
                    ->label(__('notifier::notifier.pages.settings.channels.twilio_phone_number'))
                    ->visible(fn(Get $get): bool => $get("channels.{$channel->type}.enabled") && $get("channels.{$channel->type}.provider") === 'twilio'),
                TextInput::make("channels.{$channel->type}.api_url")
                    ->label(__('notifier::notifier.pages.settings.channels.api_url'))
                    ->url()
                    ->visible(fn(Get $get): bool => $get("channels.{$channel->type}.enabled") && $get("channels.{$channel->type}.provider") === 'generic'),
                TextInput::make("channels.{$channel->type}.api_key")
                    ->label(__('notifier::notifier.pages.settings.channels.api_key'))
                    ->visible(fn(Get $get): bool => $get("channels.{$channel->type}.enabled") && ($get("channels.{$channel->type}.provider") === 'generic' || $get("channels.{$channel->type}.provider") === 'vonage')),
                TextInput::make("channels.{$channel->type}.api_secret")
                    ->label(__('notifier::notifier.pages.settings.channels.api_secret'))
                    ->password()
                    ->visible(fn(Get $get): bool => $get("channels.{$channel->type}.enabled") && ($get("channels.{$channel->type}.provider") === 'generic' || $get("channels.{$channel->type}.provider") === 'vonage')),
            ],
            'push' => [
                TextInput::make("channels.{$channel->type}.firebase_server_key")
                    ->label(__('notifier::notifier.pages.settings.channels.firebase_server_key'))
                    ->password()
                    ->visible(fn(Get $get): bool => $get("channels.{$channel->type}.enabled")),
                TextInput::make("channels.{$channel->type}.firebase_project_id")
                    ->label(__('notifier::notifier.pages.settings.channels.firebase_project_id'))
                    ->visible(fn(Get $get): bool => $get("channels.{$channel->type}.enabled")),
            ],
            'discord' => [
                TextInput::make("channels.{$channel->type}.webhook_url")
                    ->label(__('notifier::notifier.pages.settings.channels.discord_webhook_url.label'))
                    ->url()
                    ->required()
                    ->helperText(__('notifier::notifier.pages.settings.channels.discord_webhook_url.helper_text'))
                    ->visible(fn(Get $get): bool => $get("channels.{$channel->type}.enabled")),
                TextInput::make("channels.{$channel->type}.username")
                    ->label(__('notifier::notifier.pages.settings.channels.discord_username.label'))
                    ->helperText(__('notifier::notifier.pages.settings.channels.discord_username.helper_text'))
                    ->visible(fn(Get $get): bool => $get("channels.{$channel->type}.enabled")),
                TextInput::make("channels.{$channel->type}.avatar_url")
                    ->label(__('notifier::notifier.pages.settings.channels.avatar_url.label'))
                    ->url()
                    ->helperText(__('notifier::notifier.pages.settings.channels.avatar_url.helper_text'))
                    ->visible(fn(Get $get): bool => $get("channels.{$channel->type}.enabled")),
                TextInput::make("channels.{$channel->type}.color")
                    ->label(__('notifier::notifier.pages.settings.channels.embed_color.label'))
                    ->helperText(__('notifier::notifier.pages.settings.channels.embed_color.helper_text'))
                    ->numeric()
                    ->visible(fn(Get $get): bool => $get("channels.{$channel->type}.enabled")),
            ],
            default => [],
        };

        return array_merge($schema, $fields);
    }

    public function save(): void
    {
        $settings = $this->data;

        NotificationSetting::set('enabled', $settings['enabled'] ?? true, 'general');
        NotificationSetting::set('queue_name', $settings['queue_name'] ?? 'default', 'general');
        NotificationSetting::set('default_channel', $settings['default_channel'] ?? 'email', 'general');

        // Save preferences settings
        if (isset($settings['preferences'])) {
            NotificationSetting::set('preferences', $settings['preferences'], 'preferences');
        }

        // Save analytics settings
        if (isset($settings['analytics'])) {
            NotificationSetting::set('analytics', $settings['analytics'], 'analytics');
        }

        // Save rate limiting settings
        if (isset($settings['rate_limiting'])) {
            NotificationSetting::set('rate_limiting', $settings['rate_limiting'], 'rate_limiting');
        }

        // Save channel settings to channel records
        if (isset($settings['channels']) && is_array($settings['channels'])) {
            foreach ($settings['channels'] as $channelType => $channelData) {
                $channel = app(ChannelService::class)->getChannel($channelType);

                if ($channel) {
                    $isActive = $channelData['enabled'] ?? false;
                    unset($channelData['enabled']);

                    $channel->is_active = $isActive;

                    $existingSettings = $channel->settings ?? [];
                    $channel->settings = array_merge($existingSettings, $channelData);

                    $channel->save();
                }
            }
        }

        Notification::make()
            ->title(__('notifier::notifier.pages.settings.notifications.saved'))
            ->success()
            ->send();
    }

    protected function getHeaderWidgets(): array
    {
        return [
            NotificationStatsOverview::class,
        ];
    }
}

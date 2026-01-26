<?php

namespace Usamamuneerchaudhary\Notifier\Filament\Resources\NotificationChannelResource\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Usamamuneerchaudhary\Notifier\Services\ChannelService;

class NotificationChannelForm
{
    public static function configure(Schema $schema): Schema
    {
        $channelService = app(ChannelService::class);
        $enabledTypes = $channelService->getTypeOptions();

        return $schema
            ->components([
                Section::make('Channel Information')
                    ->description('Basic information about the notification channel')
                    ->schema([
                        TextInput::make('title')
                            ->label('Channel Title')
                            ->helperText('A friendly display name for this channel (e.g., "Email", "Slack", "SMS")')
                            ->placeholder('Email')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Select::make('type')
                            ->label('Channel Type')
                            ->helperText('The unique identifier for this channel type. This must match one of the supported channel types.')
                            ->options($enabledTypes)
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->searchable()
                            ->reactive()
                            ->columnSpanFull(),

                        TextInput::make('icon')
                            ->label('Icon')
                            ->helperText('Heroicon class name (e.g., heroicon-o-envelope, heroicon-o-chat-bubble-left-right). Leave empty to use default icon for channel type.')
                            ->placeholder('heroicon-o-envelope')
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->helperText('Enable or disable this channel. Inactive channels will not be used for sending notifications.')
                            ->default(true)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Channel Settings')
                    ->description('Configure channel-specific settings. These settings will be used when sending notifications through this channel.')
                    ->schema([
                        KeyValue::make('settings')
                            ->label('Settings')
                            ->keyLabel('Setting Name')
                            ->valueLabel('Setting Value')
                            ->helperText('Add key-value pairs for channel-specific configuration. Examples: For email, you might add "from_address" and "from_name". For Slack, add "webhook_url".')
                            ->reorderable()
                            ->columnSpanFull()
                            ->addable(true)
                            ->deletable(true),
                    ]),

                Section::make('Setting Examples')
                    ->description('Common settings for different channel types. Click to expand and see examples.')
                    ->collapsible()
                    ->collapsed()
                    ->schema(static::getSettingExamples($channelService)),
            ]);
    }

    protected static function getSettingExamples(ChannelService $channelService): array
    {
        $examples = [];

        if ($channelService->isTypeEnabled('email')) {
            $examples[] = Textarea::make('email_example')
                ->label('Email Channel Settings')
                ->default("from_address: noreply@example.com\nfrom_name: Your App Name")
                ->disabled()
                ->dehydrated(false)
                ->rows(3)
                ->columnSpanFull();
        }

        if ($channelService->isTypeEnabled('slack')) {
            $examples[] = Textarea::make('slack_example')
                ->label('Slack Channel Settings')
                ->default("webhook_url: https://hooks.slack.com/services/YOUR/WEBHOOK/URL\nchannel: #notifications\nusername: Notification Bot")
                ->disabled()
                ->dehydrated(false)
                ->rows(4)
                ->columnSpanFull();
        }

        if ($channelService->isTypeEnabled('sms')) {
            $examples[] = Textarea::make('sms_example')
                ->label('SMS Channel Settings (Twilio)')
                ->default("twilio_account_sid: YOUR_ACCOUNT_SID\ntwilio_auth_token: YOUR_AUTH_TOKEN\ntwilio_phone_number: +1234567890")
                ->disabled()
                ->dehydrated(false)
                ->rows(4)
                ->columnSpanFull();
        }

        if ($channelService->isTypeEnabled('push')) {
            $examples[] = Textarea::make('push_example')
                ->label('Push Channel Settings (Firebase)')
                ->default("firebase_server_key: YOUR_SERVER_KEY\nfirebase_project_id: YOUR_PROJECT_ID")
                ->disabled()
                ->dehydrated(false)
                ->rows(3)
                ->columnSpanFull();
        }

        if ($channelService->isTypeEnabled('discord')) {
            $examples[] = Textarea::make('discord_example')
                ->label('Discord Channel Settings')
                ->default("webhook_url: https://discord.com/api/webhooks/YOUR/WEBHOOK/URL\nusername: Notification Bot\navatar_url: https://example.com/avatar.png\ncolor: 3447003")
                ->disabled()
                ->dehydrated(false)
                ->rows(4)
                ->columnSpanFull();
        }

        return $examples;
    }
}

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
                Section::make(__('notifier::notifier.resources.channel.sections.information.heading'))
                    ->description(__('notifier::notifier.resources.channel.sections.information.description'))
                    ->schema([
                        TextInput::make('title')
                            ->label(__('notifier::notifier.resources.channel.fields.title.label'))
                            ->helperText(__('notifier::notifier.resources.channel.fields.title.helper_text'))
                            ->placeholder('Email')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Select::make('type')
                            ->label(__('notifier::notifier.resources.channel.fields.type.label'))
                            ->helperText(__('notifier::notifier.resources.channel.fields.type.helper_text'))
                            ->options($enabledTypes)
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->searchable()
                            ->reactive()
                            ->columnSpanFull(),

                        TextInput::make('icon')
                            ->label(__('notifier::notifier.resources.channel.fields.icon.label'))
                            ->helperText(__('notifier::notifier.resources.channel.fields.icon.helper_text'))
                            ->placeholder('heroicon-o-envelope')
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Toggle::make('is_active')
                            ->label(__('notifier::notifier.resources.channel.fields.is_active.label'))
                            ->helperText(__('notifier::notifier.resources.channel.fields.is_active.helper_text'))
                            ->default(true)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make(__('notifier::notifier.resources.channel.sections.settings.heading'))
                    ->description(__('notifier::notifier.resources.channel.sections.settings.description'))
                    ->schema([
                        KeyValue::make('settings')
                            ->label(__('notifier::notifier.resources.channel.fields.settings.label'))
                            ->keyLabel(__('notifier::notifier.resources.channel.fields.settings.key_label'))
                            ->valueLabel(__('notifier::notifier.resources.channel.fields.settings.value_label'))
                            ->helperText(__('notifier::notifier.resources.channel.fields.settings.helper_text'))
                            ->reorderable()
                            ->columnSpanFull()
                            ->addable(true)
                            ->deletable(true),
                    ]),

                Section::make(__('notifier::notifier.resources.channel.sections.examples.heading'))
                    ->description(__('notifier::notifier.resources.channel.sections.examples.description'))
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
                ->label(__('notifier::notifier.resources.channel.fields.examples.email'))
                ->default("from_address: noreply@example.com\nfrom_name: Your App Name")
                ->disabled()
                ->dehydrated(false)
                ->rows(3)
                ->columnSpanFull();
        }

        if ($channelService->isTypeEnabled('slack')) {
            $examples[] = Textarea::make('slack_example')
                ->label(__('notifier::notifier.resources.channel.fields.examples.slack'))
                ->default("webhook_url: https://hooks.slack.com/services/YOUR/WEBHOOK/URL\nchannel: #notifications\nusername: Notification Bot")
                ->disabled()
                ->dehydrated(false)
                ->rows(4)
                ->columnSpanFull();
        }

        if ($channelService->isTypeEnabled('sms')) {
            $examples[] = Textarea::make('sms_example')
                ->label(__('notifier::notifier.resources.channel.fields.examples.sms'))
                ->default("twilio_account_sid: YOUR_ACCOUNT_SID\ntwilio_auth_token: YOUR_AUTH_TOKEN\ntwilio_phone_number: +1234567890")
                ->disabled()
                ->dehydrated(false)
                ->rows(4)
                ->columnSpanFull();
        }

        if ($channelService->isTypeEnabled('push')) {
            $examples[] = Textarea::make('push_example')
                ->label(__('notifier::notifier.resources.channel.fields.examples.push'))
                ->default("firebase_server_key: YOUR_SERVER_KEY\nfirebase_project_id: YOUR_PROJECT_ID")
                ->disabled()
                ->dehydrated(false)
                ->rows(3)
                ->columnSpanFull();
        }

        if ($channelService->isTypeEnabled('discord')) {
            $examples[] = Textarea::make('discord_example')
                ->label(__('notifier::notifier.resources.channel.fields.examples.discord'))
                ->default("webhook_url: https://discord.com/api/webhooks/YOUR/WEBHOOK/URL\nusername: Notification Bot\navatar_url: https://example.com/avatar.png\ncolor: 3447003")
                ->disabled()
                ->dehydrated(false)
                ->rows(4)
                ->columnSpanFull();
        }

        return $examples;
    }
}

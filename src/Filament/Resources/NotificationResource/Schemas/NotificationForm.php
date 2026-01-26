<?php

namespace Usamamuneerchaudhary\Notifier\Filament\Resources\NotificationResource\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class NotificationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        Select::make('notification_template_id')
                            ->relationship('template', 'name')
                            ->label(__('notifier::notifier.resources.notification.fields.template'))
                            ->required()
                            ->searchable(),
                        Select::make('user_id')
                            ->relationship('user', 'name')
                            ->label(__('notifier::notifier.resources.notification.fields.user'))
                            ->required()
                            ->searchable(),
                        Select::make('channel')
                            ->label(__('notifier::notifier.resources.notification.fields.channel'))
                            ->options([
                                'email' => 'Email',
                                'sms' => 'SMS',
                                'slack' => 'Slack',
                                'discord' => 'Discord',
                                'push' => 'Push Notification',
                            ])
                            ->required(),
                        TextInput::make('subject')
                            ->label(__('notifier::notifier.resources.notification.fields.subject'))
                            ->maxLength(255),
                        Textarea::make('content')
                            ->label(__('notifier::notifier.resources.notification.fields.content'))
                            ->rows(10)
                            ->readonly(),
                        KeyValue::make('data')
                            ->label(__('notifier::notifier.resources.notification.fields.data'))
                            ->keyLabel('Key')
                            ->valueLabel('Value'),
                        Select::make('status')
                            ->label(__('notifier::notifier.resources.notification.fields.status'))
                            ->options([
                                'pending' => __('notifier::notifier.resources.notification.status.pending'),
                                'sent' => __('notifier::notifier.resources.notification.status.sent'),
                                'failed' => __('notifier::notifier.resources.notification.status.failed'),
                            ])
                            ->required(),
                        DateTimePicker::make('scheduled_at')
                            ->label(__('notifier::notifier.resources.notification.fields.scheduled_at'))
                            ->readonly(),
                        DateTimePicker::make('sent_at')
                            ->label(__('notifier::notifier.resources.notification.fields.sent_at'))
                            ->readonly(),
                        Textarea::make('error')
                            ->label(__('notifier::notifier.resources.notification.fields.error'))
                            ->rows(3)
                            ->readonly()
                            ->visible(fn($record) => $record && $record->status === 'failed'),
                    ])
            ]);
    }
}

<?php

namespace Usamamuneerchaudhary\Notifier\Filament\Resources\NotificationTemplateResource\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Usamamuneerchaudhary\Notifier\Services\EventService;

class NotificationTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('notifier::notifier.resources.template.sections.information.heading'))
                    ->description(__('notifier::notifier.resources.template.sections.information.description'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('notifier::notifier.resources.template.fields.name.label'))
                            ->helperText(__('notifier::notifier.resources.template.fields.name.helper_text'))
                            ->placeholder('welcome-email')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Select::make('event_key')
                            ->label(__('notifier::notifier.resources.template.fields.event_key.label'))
                            ->helperText(__('notifier::notifier.resources.template.fields.event_key.helper_text'))
                            ->options(fn() => app(EventService::class)->options())
                            ->searchable()
                            ->required()
                            ->columnSpanFull(),

                        TextInput::make('subject')
                            ->label(__('notifier::notifier.resources.template.fields.subject.label'))
                            ->helperText(__('notifier::notifier.resources.template.fields.subject.helper_text'))
                            ->placeholder('Welcome to {{app_name}}, {{name}}!')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Toggle::make('is_active')
                            ->label(__('notifier::notifier.resources.template.fields.is_active.label'))
                            ->helperText(__('notifier::notifier.resources.template.fields.is_active.helper_text'))
                            ->default(true)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make(__('notifier::notifier.resources.template.sections.content.heading'))
                    ->description(__('notifier::notifier.resources.template.sections.content.description'))
                    ->schema([
                        Textarea::make('content')
                            ->label(__('notifier::notifier.resources.template.fields.content.label'))
                            ->helperText(__('notifier::notifier.resources.template.fields.content.helper_text'))
                            ->placeholder("Hi {{name}},\n\nWelcome to {{app_name}}! We're excited to have you on board.\n\nBest regards,\nThe {{app_name}} Team")
                            ->required()
                            ->rows(12)
                            ->columnSpanFull(),
                    ]),

                Section::make(__('notifier::notifier.resources.template.sections.variables.heading'))
                    ->description(__('notifier::notifier.resources.template.sections.variables.description'))
                    ->schema([
                        KeyValue::make('variables')
                            ->label(__('notifier::notifier.resources.template.fields.variables.label'))
                            ->keyLabel(__('notifier::notifier.resources.template.fields.variables.key_label'))
                            ->valueLabel(__('notifier::notifier.resources.template.fields.variables.value_label'))
                            ->helperText(__('notifier::notifier.resources.template.fields.variables.helper_text'))
                            ->addable(true)
                            ->deletable(true)
                            ->reorderable()
                            ->columnSpanFull(),
                    ]),

                Section::make(__('notifier::notifier.resources.template.sections.examples.heading'))
                    ->description(__('notifier::notifier.resources.template.sections.examples.description'))
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Textarea::make('email_example')
                            ->label(__('notifier::notifier.resources.template.fields.examples.email'))
                            ->default("Subject: Welcome to {{app_name}}, {{name}}!\n\nContent:\nHi {{name}},\n\nWelcome to {{app_name}}! We're excited to have you on board.\n\nYour account has been created successfully. You can now log in using:\nEmail: {{email}}\n\nBest regards,\nThe {{app_name}} Team")
                            ->disabled()
                            ->dehydrated(false)
                            ->rows(8)
                            ->columnSpanFull(),

                        Textarea::make('sms_example')
                            ->label(__('notifier::notifier.resources.template.fields.examples.sms'))
                            ->default("Subject: Order Confirmation\n\nContent:\nHi {{name}}, your order #{{order_number}} has been confirmed. Total: {{amount}}. Track at {{tracking_url}}")
                            ->disabled()
                            ->dehydrated(false)
                            ->rows(5)
                            ->columnSpanFull(),

                        Textarea::make('slack_example')
                            ->label(__('notifier::notifier.resources.template.fields.examples.slack'))
                            ->default("Subject: New Project Created\n\nContent:\n🎉 New project created!\n\nProject: {{project_name}}\nCreated by: {{user_name}}\nView: {{project_url}}")
                            ->disabled()
                            ->dehydrated(false)
                            ->rows(6)
                            ->columnSpanFull(),

                        Textarea::make('discord_example')
                            ->label(__('notifier::notifier.resources.template.fields.examples.discord'))
                            ->default("Subject: New Project Created\n\nContent:\n🎉 New project created!\n\nProject: {{project_name}}\nCreated by: {{user_name}}\nView: {{project_url}}")
                            ->disabled()
                            ->dehydrated(false)
                            ->rows(6)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}

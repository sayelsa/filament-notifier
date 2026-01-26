<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Notification Settings
    |--------------------------------------------------------------------------
    |
    | This option controls the default notification settings for the package.
    |
    */
    'defaults' => [
        'queue' => env('NOTIFIER_QUEUE', 'default'),
        'timeout' => env('NOTIFIER_TIMEOUT', 30),
        'retries' => env('NOTIFIER_RETRIES', 3),
    ],

    /*
    |--------------------------------------------------------------------------
    | Available Channel Types
    |--------------------------------------------------------------------------
    |
    | Define which notification channel types are available in your application.
    | Set 'enabled' to false to hide a channel type from the admin panel.
    | Only enabled channel types can be configured by admins.
    |
    */
    'channels' => [
        'email' => [
            'enabled' => true,
            'label' => 'Email - Send notifications via email',
        ],
        'slack' => [
            'enabled' => true,
            'label' => 'Slack - Send notifications to Slack workspace',
        ],
        'discord' => [
            'enabled' => false,
            'label' => 'Discord - Send notifications via webhook',
        ],
        'sms' => [
            'enabled' => false,
            'label' => 'SMS - Send text message notifications',
        ],
        'push' => [
            'enabled' => false,
            'label' => 'Push - Firebase FCM notifications',
        ],
        'database' => [
            'enabled' => true,
            'label' => 'Database - Store notifications in database',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Events
    |--------------------------------------------------------------------------
    |
    | Define all notification events that can be triggered in your application.
    | These are system-wide and not editable by tenants. Each event key is used
    | when calling: app('notifier')->send($user, 'event.key', $data)
    |
    | Format:
    | 'event.key' => [
    |     'name' => 'Display Name',
    |     'group' => 'Group Name',
    |     'description' => 'When this event is triggered',
    | ],
    |
    */
    'events' => [
        'user.registered' => [
            'name' => 'User Registered',
            'group' => 'Users',
            'description' => 'Triggered when a new user registers',
        ],
        'user.password_reset' => [
            'name' => 'Password Reset Requested',
            'group' => 'Users',
            'description' => 'Triggered when a user requests a password reset',
        ],
        'reminder.email' => [
            'name' => 'Reminder Email',
            'group' => 'Reminders',
            'description' => 'Triggered for scheduled reminder emails',
        ],
        'test.event' => [
            'name' => 'Test Event',
            'group' => 'Test',
            'description' => 'Used for testing purposes',
        ],
        // Add your custom events here
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    |
    | These settings are stored in the database (notifier_settings table) and
    | can be managed through the Filament admin panel. The values below are
    | used as fallbacks if no database values exist.
    |
    | Note: Channels, Events, and Templates are now managed entirely through
    | the database and Filament admin interface.
    |
    */
    'settings' => [
        'preferences' => [
            'enabled' => env('NOTIFIER_PREFERENCES_ENABLED', true),
            'default_channels' => ['email'],
            'allow_override' => env('NOTIFIER_ALLOW_OVERRIDE', true),
        ],
        'analytics' => [
            'enabled' => env('NOTIFIER_ANALYTICS_ENABLED', true),
            'track_opens' => env('NOTIFIER_TRACK_OPENS', true),
            'track_clicks' => env('NOTIFIER_TRACK_CLICKS', true),
            'retention_days' => env('NOTIFIER_RETENTION_DAYS', 90),
        ],
        'rate_limiting' => [
            'enabled' => env('NOTIFIER_RATE_LIMITING_ENABLED', true),
            'max_per_minute' => env('NOTIFIER_MAX_PER_MINUTE', 60),
            'max_per_hour' => env('NOTIFIER_MAX_PER_HOUR', 1000),
            'max_per_day' => env('NOTIFIER_MAX_PER_DAY', 10000),
        ],
        'template_cache' => env('NOTIFIER_TEMPLATE_CACHE', false),
        'log_unreplaced_variables' => env('NOTIFIER_LOG_UNREPLACED_VARIABLES', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Multi-Tenancy Settings
    |--------------------------------------------------------------------------
    |
    | Configure multi-tenancy support for the notifier package. When enabled,
    | all notification data will be scoped by tenant_id. When disabled, the
    | package works in single-tenant mode.
    |
    */
    'multitenancy' => [
        'enabled' => env('NOTIFIER_MULTITENANCY_ENABLED', false),

        /*
        |--------------------------------------------------------------------------
        | Tenant Model
        |--------------------------------------------------------------------------
        |
        | The fully qualified class name of your tenant model (e.g., App\Models\Team,
        | App\Models\Organization). This model will be used for tenant relationships.
        |
        */
        'tenant_model' => env('NOTIFIER_TENANT_MODEL', null),

        /*
        |--------------------------------------------------------------------------
        | Tenant Column
        |--------------------------------------------------------------------------
        |
        | The column name used in database tables to store the tenant identifier.
        |
        */
        'tenant_column' => env('NOTIFIER_TENANT_COLUMN', 'tenant_id'),

        /*
        |--------------------------------------------------------------------------
        | Tenant Resolver
        |--------------------------------------------------------------------------
        |
        | How to resolve the current tenant. Options:
        | - 'filament': Uses Filament::getTenant() (recommended for Filament panels)
        | - 'session': Reads tenant_id from session
        | - A fully qualified class name implementing TenantResolverInterface
        |
        */
        'resolver' => env('NOTIFIER_TENANT_RESOLVER', 'filament'),

        /*
        |--------------------------------------------------------------------------
        | Ownership Relationship Name
        |--------------------------------------------------------------------------
        |
        | The name of the relationship that Filament uses for tenant ownership.
        | If null, it will be auto-detected from the tenant model name.
        | Example: for App\Models\Org, it would be 'org'.
        |
        */
        'ownership_relationship' => env('NOTIFIER_OWNERSHIP_RELATIONSHIP', null),
    ],
];

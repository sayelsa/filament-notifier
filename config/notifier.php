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

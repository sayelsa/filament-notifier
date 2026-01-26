<?php

return [
    'resources' => [
        'channel' => [
            'label' => 'Notification Channel',
            'plural_label' => 'Notification Channels',
            'sections' => [
                'information' => [
                    'heading' => 'Channel Information',
                    'description' => 'Basic information about the notification channel',
                ],
                'settings' => [
                    'heading' => 'Channel Settings',
                    'description' => 'Configure channel-specific settings. These settings will be used when sending notifications through this channel.',
                ],
                'examples' => [
                    'heading' => 'Setting Examples',
                    'description' => 'Common settings for different channel types. Click to expand and see examples.',
                ],
            ],
            'fields' => [
                'title' => [
                    'label' => 'Channel Title',
                    'helper_text' => 'A friendly display name for this channel (e.g., "Email", "Slack", "SMS")',
                ],
                'type' => [
                    'label' => 'Channel Type',
                    'helper_text' => 'The unique identifier for this channel type. This must match one of the supported channel types.',
                ],
                'icon' => [
                    'label' => 'Icon',
                    'helper_text' => 'Heroicon class name (e.g., heroicon-o-envelope, heroicon-o-chat-bubble-left-right). Leave empty to use default icon for channel type.',
                ],
                'is_active' => [
                    'label' => 'Active',
                    'helper_text' => 'Enable or disable this channel. Inactive channels will not be used for sending notifications.',
                ],
                'settings' => [
                    'label' => 'Settings',
                    'key_label' => 'Setting Name',
                    'value_label' => 'Setting Value',
                    'helper_text' => 'Add key-value pairs for channel-specific configuration. Examples: For email, you might add "from_address" and "from_name". For Slack, add "webhook_url".',
                ],
                'examples' => [
                    'email' => 'Email Channel Settings',
                    'sms' => 'SMS Channel Settings (Twilio)',
                    'slack' => 'Slack Channel Settings',
                    'discord' => 'Discord Channel Settings',
                    'push' => 'Push Channel Settings (Firebase)',
                ],
            ],
        ],
        'notification' => [
            'label' => 'Notification',
            'plural_label' => 'Notifications',
            'fields' => [
                'template' => 'Template',
                'user' => 'User',
                'channel' => 'Channel',
                'subject' => 'Subject',
                'content' => 'Content',
                'status' => 'Status',
                'scheduled_at' => 'Scheduled At',
                'sent_at' => 'Sent At',
                'error' => 'Error',
                'data' => 'Data',
            ],
            'status' => [
                'pending' => 'Pending',
                'sent' => 'Sent',
                'failed' => 'Failed',
            ],
        ],
        'template' => [
            'label' => 'Notification Template',
            'plural_label' => 'Notification Templates',
            'sections' => [
                'information' => [
                    'heading' => 'Template Information',
                    'description' => 'Basic information about the notification template',
                ],
                'content' => [
                    'heading' => 'Template Content',
                    'description' => 'The actual content of the notification template',
                ],
                'variables' => [
                    'heading' => 'Template Variables',
                    'description' => 'Define the variables that can be used in this template. These help document what data should be passed when sending notifications.',
                ],
                'examples' => [
                    'heading' => 'Template Examples',
                    'description' => 'Example templates for different use cases. Click to expand.',
                ],
            ],
            'fields' => [
                'name' => [
                    'label' => 'Template Name',
                    'helper_text' => 'A friendly display name for this template (e.g., "Welcome Email", "Order Confirmation"). This is also used as the unique identifier to reference the template in code.',
                ],
                'event_key' => [
                    'label' => 'Linked Event',
                    'helper_text' => 'Link this template to a specific notification event. This is required and helps organize templates. The template will be used when this event is triggered.',
                ],
                'subject' => [
                    'label' => 'Subject Line',
                    'helper_text' => 'The subject line for email notifications. For SMS/Slack/Discord, this may be used as a title. Use {{variable}} for dynamic content.',
                ],
                'is_active' => [
                    'label' => 'Active',
                    'helper_text' => 'Enable or disable this template. Inactive templates will not be used for sending notifications.',
                ],
                'content' => [
                    'label' => 'Template Content',
                    'helper_text' => 'The main content of your notification. Use {{variable}} syntax to insert dynamic values. Example: "Hi {{name}}, welcome to {{app_name}}!"',
                ],
                'variables' => [
                    'label' => 'Variables',
                    'key_label' => 'Variable Name',
                    'value_label' => 'Description',
                    'helper_text' => 'Document the variables used in your template. Key should match the variable name (without {{}}), value should describe what it represents. Example: name → "User\'s full name", app_name → "Application name"',
                ],
                'examples' => [
                    'email' => 'Email Template Example',
                    'sms' => 'SMS Template Example',
                    'slack' => 'Slack Template Example',
                    'discord' => 'Discord Template Example',
                ],
            ],
        ],
    ],
    'pages' => [
        'dashboard' => [
            'navigation_label' => 'Dashboard',
            'title' => 'Notifier Dashboard',
        ],
        'event_channels' => [
            'navigation_label' => 'Event Channels',
            'title' => 'Event Channel Configuration',
            'sections' => [
                'general' => 'General',
                'description' => 'Configure which channels should be used for each event.',
            ],
            'notifications' => [
                'saved' => 'Configuration Saved',
                'saved_body' => 'Successfully updated channel configuration for :count event(s).',
            ],
        ],
        'preferences' => [
            'navigation_label' => 'Notification Preferences',
            'title' => 'Notification Preferences',
            'sections' => [
                'general' => 'General',
            ],
            'notifications' => [
                'disabled' => 'Preferences Disabled',
                'disabled_body' => 'User preference override is disabled by administrator.',
                'saved' => 'Preferences Saved',
                'saved_body' => 'Successfully updated :count notification preference(s).',
            ],
        ],
        'settings' => [
            'navigation_label' => 'Settings',
            'title' => 'Notification Settings',
            'tabs' => [
                'general' => 'General',
                'preferences' => 'Preferences',
                'analytics' => 'Analytics',
                'rate_limiting' => 'Rate Limiting',
            ],
            'fields' => [
                'enable_notifications' => 'Enable Notifications',
                'queue_name' => 'Queue Name',
                'default_channel' => 'Default Channel',
            ],
            'sections' => [
                'user_preferences' => [
                    'heading' => 'User Preferences',
                    'description' => 'Configure default user notification preferences',
                ],
                'analytics' => [
                    'heading' => 'Analytics Settings',
                    'description' => 'Configure notification analytics and tracking',
                ],
                'rate_limiting' => [
                    'heading' => 'Rate Limiting Settings',
                    'description' => 'Configure rate limits for notifications to prevent abuse',
                ],
            ],
            'preferences' => [
                'enable' => 'Enable User Preferences',
                'default_channels' => 'Default Channels',
                'allow_override' => [
                    'label' => 'Allow Users to Override Preferences',
                    'helper_text' => 'If enabled, users can customize their notification preferences',
                ],
            ],
            'analytics' => [
                'enable' => 'Enable Analytics',
                'track_opens' => 'Track Email Opens',
                'track_clicks' => 'Track Link Clicks',
                'retention_days' => 'Data Retention (Days)',
            ],
            'rate_limiting' => [
                'enable' => 'Enable Rate Limiting',
                'max_per_minute' => 'Max Per Minute',
                'max_per_hour' => 'Max Per Hour',
                'max_per_day' => 'Max Per Day',
            ],
            'channels' => [
                'enable' => 'Enable :channel',
                'from_address' => 'From Address',
                'from_name' => 'From Name',
                'smtp_host' => 'SMTP Host',
                'smtp_port' => 'SMTP Port',
                'smtp_encryption' => 'Encryption',
                'smtp_username' => 'SMTP Username',
                'smtp_password' => 'SMTP Password',
                'webhook_url' => 'Webhook URL',
                'channel' => 'Slack Channel',
                'username' => 'Bot Username',
                'provider' => 'SMS Provider',
                'twilio_account_sid' => 'Twilio Account SID',
                'twilio_auth_token' => 'Twilio Auth Token',
                'twilio_phone_number' => 'Twilio Phone Number',
                'api_url' => 'API URL',
                'api_key' => 'API Key',
                'api_secret' => 'API Secret',
                'firebase_server_key' => 'Firebase Server Key',
                'firebase_project_id' => 'Firebase Project ID',
                'discord_webhook_url' => [
                    'label' => 'Discord Webhook URL',
                    'helper_text' => 'Get this from your Discord server settings > Integrations > Webhooks',
                ],
                'discord_username' => [
                    'label' => 'Bot Username',
                    'helper_text' => 'Optional: Custom username for the webhook',
                ],
                'avatar_url' => [
                    'label' => 'Avatar URL',
                    'helper_text' => 'Optional: URL for the webhook avatar',
                ],
                'embed_color' => [
                    'label' => 'Embed Color',
                    'helper_text' => 'Optional: Decimal color code for embed (e.g., 3447003 for blue)',
                ],
            ],
            'notifications' => [
                'saved' => 'Settings saved successfully',
            ],
        ],
    ],
    'widgets' => [
        'analytics' => [
            'heading' => 'Engagement Analytics (Last 7 Days)',
            'opens' => 'Opens',
            'clicks' => 'Clicks',
            'open_rate' => 'Open Rate %',
            'click_rate' => 'Click Rate %',
        ],
        'performance' => [
            'heading' => 'Channel Performance',
            'sent' => 'Sent',
            'opened' => 'Opened',
            'clicked' => 'Clicked',
        ],
        'engagement' => [
            'analytics_disabled' => 'Analytics Disabled',
            'enable_in_settings' => 'Enable analytics in settings to view engagement metrics',
            'total_opens' => 'Total Opens',
            'unique_opens' => ':count unique opens',
            'open_rate' => 'Open Rate',
            'emails_opened' => ':opened of :sent emails opened',
            'total_clicks' => 'Total Clicks',
            'unique_clicks' => ':count unique clicks',
            'click_rate' => 'Click Rate',
            'emails_clicked' => ':clicked of :sent emails clicked',
            'click_through_rate' => 'Click-Through Rate',
            'clicks_per_open' => 'Clicks per open',
        ],
        'overview' => [
            'total' => 'Total Notifications',
            'all_time' => 'All time notifications',
            'success_rate' => 'Success Rate',
            'sent_successfully' => ':sent of :total sent successfully',
            'pending' => 'Pending Notifications',
            'awaiting' => 'Awaiting delivery',
            'failed' => 'Failed Notifications',
            'failed_delivery' => 'Failed to deliver',
            'active_channels' => 'Active Channels',
            'enabled_channels' => 'Enabled notification channels',
        ],
        'time_series' => [
            'heading' => 'Notifications Over Time',
        ],
        'rate_limiting' => [
            'heading' => 'Rate Limiting Status',
            'disabled' => 'Rate Limiting Disabled',
            'disabled_desc' => 'Rate limiting is currently disabled',
            'per_minute' => 'Per Minute',
            'per_hour' => 'Per Hour',
            'per_day' => 'Per Day',
            'used' => '% used',
        ],
    ],
];

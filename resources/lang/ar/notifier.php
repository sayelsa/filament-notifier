<?php

return [
    'resources' => [
        'channel' => [
            'label' => 'قناة الإشعار',
            'plural_label' => 'قنوات الإشعارات',
            'sections' => [
                'information' => [
                    'heading' => 'معلومات القناة',
                    'description' => 'المعلومات الأساسية حول قناة الإشعار',
                ],
                'settings' => [
                    'heading' => 'إعدادات القناة',
                    'description' => 'تكوين الإعدادات الخاصة بالقناة. سيتم استخدام هذه الإعدادات عند إرسال الإشعارات عبر هذه القناة.',
                ],
                'examples' => [
                    'heading' => 'أمثلة الإعدادات',
                    'description' => 'إعدادات شائعة لأنواع القنوات المختلفة. انقر للتوسيع ورؤية الأمثلة.',
                ],
            ],
            'fields' => [
                'title' => [
                    'label' => 'عنوان القناة',
                    'helper_text' => 'اسم عرض مألوف لهذه القناة (مثال: "البريد الإلكتروني"، "سلاك"، "رسائل نصية")',
                ],
                'type' => [
                    'label' => 'نوع القناة',
                    'helper_text' => 'المعرف الفريد لنوع القناة هذا. يجب أن يطابق أحد أنواع القنوات المدعومة.',
                ],
                'icon' => [
                    'label' => 'الأيقونة',
                    'helper_text' => 'اسم فئة Heroicon (مثال: heroicon-o-envelope). اتركها فارغة لاستخدام الأيقونة الافتراضية لنوع القناة.',
                ],
                'is_active' => [
                    'label' => 'نشط',
                    'helper_text' => 'تفعيل أو تعطيل هذه القناة. القنوات غير النشطة لن تُستخدم لإرسال الإشعارات.',
                ],
                'settings' => [
                    'label' => 'الإعدادات',
                    'key_label' => 'اسم الإعداد',
                    'value_label' => 'قيمة الإعداد',
                    'helper_text' => 'أضف أزواج المفتاح-القيمة لتكوين القناة. أمثلة: للبريد الإلكتروني، قد تضيف "from_address" و "from_name".',
                ],
                'examples' => [
                    'email' => 'إعدادات قناة البريد الإلكتروني',
                    'sms' => 'إعدادات قناة الرسائل النصية (Twilio)',
                    'slack' => 'إعدادات قناة سلاك',
                    'discord' => 'إعدادات قناة ديسكورد',
                    'push' => 'إعدادات قناة الإشعارات المنبثقة (Firebase)',
                ],
            ],
        ],
        'notification' => [
            'label' => 'إشعار',
            'plural_label' => 'إشعارات',
            'fields' => [
                'template' => 'القالب',
                'user' => 'المستخدم',
                'channel' => 'القناة',
                'subject' => 'الموضوع',
                'content' => 'المحتوى',
                'status' => 'الحالة',
                'scheduled_at' => 'مجدول في',
                'sent_at' => 'تم الإرسال في',
                'error' => 'خطأ',
                'data' => 'البيانات',
            ],
            'status' => [
                'pending' => 'قيد الانتظار',
                'sent' => 'تم الإرسال',
                'failed' => 'فشل',
            ],
        ],
        'template' => [
            'label' => 'قالب الإشعار',
            'plural_label' => 'قوالب الإشعارات',
            'sections' => [
                'information' => [
                    'heading' => 'معلومات القالب',
                    'description' => 'المعلومات الأساسية حول قالب الإشعار',
                ],
                'content' => [
                    'heading' => 'محتوى القالب',
                    'description' => 'المحتوى الفعلي لقالب الإشعار',
                ],
                'variables' => [
                    'heading' => 'متغيرات القالب',
                    'description' => 'تحديد المتغيرات التي يمكن استخدامها في هذا القالب. تساعد هذه في توثيق البيانات التي يجب تمريرها عند إرسال الإشعارات.',
                ],
                'examples' => [
                    'heading' => 'أمثلة القوالب',
                    'description' => 'قوالب نموذجية لحالات استخدام مختلفة. انقر للتوسيع.',
                ],
            ],
            'fields' => [
                'name' => [
                    'label' => 'اسم القالب',
                    'helper_text' => 'اسم عرض مألوف لهذا القالب. يستخدم أيضًا كمعرف فريد للإشارة إلى القالب في الكود.',
                ],
                'event_key' => [
                    'label' => 'الحدث المرتبط',
                    'helper_text' => 'ربط هذا القالب بحدث إشعار محدد. هذا مطلوب ويساعد في تنظيم القوالب.',
                ],
                'subject' => [
                    'label' => 'سطر الموضوع',
                    'helper_text' => 'سطر الموضوع لإشعارات البريد الإلكتروني. للرسائل النصية/سلاك/ديسكورد، قد يستخدم كعنوان.',
                ],
                'is_active' => [
                    'label' => 'نشط',
                    'helper_text' => 'تفعيل أو تعطيل هذا القالب.',
                ],
                'content' => [
                    'label' => 'محتوى القالب',
                    'helper_text' => 'المحتوى الرئيسي لإشعارك. استخدم صياغة {{variable}} لإدراج قيم ديناميكية.',
                ],
                'variables' => [
                    'label' => 'المتغيرات',
                    'key_label' => 'اسم المتغير',
                    'value_label' => 'الوصف',
                    'helper_text' => 'وثّق المتغيرات المستخدمة في قالبك.',
                ],
                'examples' => [
                    'email' => 'مثال قالب البريد الإلكتروني',
                    'sms' => 'مثال قالب الرسائل النصية',
                    'slack' => 'مثال قالب سلاك',
                    'discord' => 'مثال قالب ديسكورد',
                ],
            ],
        ],
    ],
    'pages' => [
        'event_channels' => [
            'navigation_label' => 'قنوات الأحداث',
            'title' => 'تكوين قنوات الأحداث',
            'sections' => [
                'general' => 'عام',
                'description' => 'تكوين القنوات التي يجب استخدامها لكل حدث.',
            ],
            'notifications' => [
                'saved' => 'تم حفظ التكوين',
                'saved_body' => 'تم تحديث تكوين القناة بنجاح لـ :count حدث.',
            ],
        ],
        'preferences' => [
            'navigation_label' => 'تفضيلات الإشعارات',
            'title' => 'تفضيلات الإشعارات',
            'sections' => [
                'general' => 'عام',
            ],
            'notifications' => [
                'disabled' => 'التفضيلات معطلة',
                'disabled_body' => 'تجاوز تفضيلات المستخدم معطل من قبل المسؤول.',
                'saved' => 'تم حفظ التفضيلات',
                'saved_body' => 'تم تحديث :count تفضيلات إشعار بنجاح.',
            ],
        ],
        'settings' => [
            'navigation_label' => 'الإعدادات',
            'title' => 'إعدادات الإشعارات',
            'tabs' => [
                'general' => 'عام',
                'preferences' => 'التفضيلات',
                'analytics' => 'التحليلات',
                'rate_limiting' => 'تحديد المعدل',
            ],
            'fields' => [
                'enable_notifications' => 'تفعيل الإشعارات',
                'queue_name' => 'اسم الطابور',
                'default_channel' => 'القناة الافتراضية',
            ],
            'sections' => [
                'user_preferences' => [
                    'heading' => 'تفضيلات المستخدم',
                    'description' => 'تكوين تفضيلات إشعارات المستخدم الافتراضية',
                ],
                'analytics' => [
                    'heading' => 'إعدادات التحليلات',
                    'description' => 'تكوين تحليلات وتتبع الإشعارات',
                ],
                'rate_limiting' => [
                    'heading' => 'إعدادات تحديد المعدل',
                    'description' => 'تكوين حدود المعدل للإشعارات لمنع الاستخدام المسىء',
                ],
            ],
            'preferences' => [
                'enable' => 'تفعيل تفضيلات المستخدم',
                'default_channels' => 'القنوات الافتراضية',
                'allow_override' => [
                    'label' => 'السماح للمستخدمين بتجاوز التفضيلات',
                    'helper_text' => 'إذا تم تفعيلها، يمكن للمستخدمين تخصيص تفضيلات إشعاراتهم',
                ],
            ],
            'analytics' => [
                'enable' => 'تفعيل التحليلات',
                'track_opens' => 'تتبع فتح البريد الإلكتروني',
                'track_clicks' => 'تتبع النقر على الروابط',
                'retention_days' => 'الاحتفاظ بالبيانات (أيام)',
            ],
            'rate_limiting' => [
                'enable' => 'تفعيل تحديد المعدل',
                'max_per_minute' => 'الحد الأقصى في الدقيقة',
                'max_per_hour' => 'الحد الأقصى في الساعة',
                'max_per_day' => 'الحد الأقصى في اليوم',
            ],
            'channels' => [
                'enable' => 'تفعيل :channel',
                'from_address' => 'من عنوان',
                'from_name' => 'من اسم',
                'webhook_url' => 'عنوان URL للخطاف (Webhook)',
                'channel' => 'قناة سلاك',
                'username' => 'اسم مستخدم البوت',
                'provider' => 'موفر الرسائل النصية',
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
                    'helper_text' => 'احصل على هذا من إعدادات خادم ديسكورد > التكاملات > الخطافات',
                ],
                'discord_username' => [
                    'label' => 'اسم مستخدم البوت',
                    'helper_text' => 'اختياري: اسم مستخدم مخصص للخطاف',
                ],
                'avatar_url' => [
                    'label' => 'عنوان URL للصورة الرمزية',
                    'helper_text' => 'اختياري: رابط للصورة الرمزية للخطاف',
                ],
                'embed_color' => [
                    'label' => 'لون التضمين',
                    'helper_text' => 'اختياري: كود اللون العشري للتضمين',
                ],
            ],
            'notifications' => [
                'saved' => 'تم حفظ الإعدادات بنجاح',
            ],
        ],
    ],
    'widgets' => [
        'analytics' => [
            'heading' => 'تحليلات التفاعل (آخر 7 أيام)',
            'opens' => 'الفتح',
            'clicks' => 'النقرات',
            'open_rate' => 'معدل الفتح %',
            'click_rate' => 'معدل النقر %',
        ],
        'performance' => [
            'heading' => 'أداء القناة',
            'sent' => 'تم الإرسال',
            'opened' => 'تم الفتح',
            'clicked' => 'تم النقر',
        ],
        'engagement' => [
            'analytics_disabled' => 'التحليلات معطلة',
            'enable_in_settings' => 'قم بتفعيل التحليلات في الإعدادات لعرض مقاييس التفاعل',
            'total_opens' => 'إجمالي الفتح',
            'unique_opens' => ':count فتح فريد',
            'open_rate' => 'معدل الفتح',
            'emails_opened' => ':opened من :sent بريد إلكتروني تم فتحه',
            'total_clicks' => 'إجمالي النقرات',
            'unique_clicks' => ':count نقرة فريدة',
            'click_rate' => 'معدل النقر',
            'emails_clicked' => ':clicked من :sent بريد إلكتروني تم النقر عليه',
            'click_through_rate' => 'معدل النقر إلى الظهور',
            'clicks_per_open' => 'النقرات لكل فتح',
        ],
        'overview' => [
            'total' => 'إجمالي الإشعارات',
            'all_time' => 'الإشعارات طوال الوقت',
            'success_rate' => 'معدل النجاح',
            'sent_successfully' => ':sent من :total أرسلت بنجاح',
            'pending' => 'إشعارات قيد الانتظار',
            'awaiting' => 'في انتظار التسليم',
            'failed' => 'إشعارات فاشلة',
            'failed_delivery' => 'فشل التسليم',
            'active_channels' => 'القنوات النشطة',
            'enabled_channels' => 'قنوات الإشعارات المفعلة',
        ],
        'time_series' => [
            'heading' => 'الإشعارات بمرور الوقت',
        ],
        'rate_limiting' => [
            'heading' => 'حالة تحديد المعدل',
            'disabled' => 'تحديد المعدل معطل',
            'disabled_desc' => 'تحديد المعدل معطل حاليًا',
            'per_minute' => 'في الدقيقة',
            'per_hour' => 'في الساعة',
            'per_day' => 'في اليوم',
            'used' => '% مستخدم',
        ],
    ],
];

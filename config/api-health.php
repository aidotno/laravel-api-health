<?php

return [
    'checkers' => [
        // \App\Checkers\SomeServiceChecker::class,
    ],

    'cache_driver' => 'file',

    'storage_path' => storage_path('laravel-api-checker'),

    'notifications' => [
        'resend_failed_notification_after_minutes' => 60,

        'only_send_failed_notification_after_successive_failures' => 0,

        'default_failed_notification' => \Pbmedia\ApiHealth\Notifications\CheckerHasFailed::class,

        'default_recovered_notification' => \Pbmedia\ApiHealth\Notifications\CheckerHasRecovered::class,

        'via' => [
            // 'mail', 'slack',
        ],

        'notifiable' => \Pbmedia\ApiHealth\Notifications\Notifiable::class,

        'mail' => [
            'to' => 'your@example.com',
        ],

        'slack' => [
            'webhook_url' => '',

            'channel' => null,

            'username' => null,

            'icon' => null,
        ],
    ],
];

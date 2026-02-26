<?php

/**
 * Mail Configuration
 */

return [
    'default' => 'smtp',
    'mailers' => [
        'smtp' => [
            'transport' => 'smtp',
            'host' => env('MAIL_HOST', 'smtp.gmail.com'),
            'port' => env('MAIL_PORT', 587),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'PLACEHOLDER_SECRET_VALUEtimeout' => null,
            'auth_mode' => null,
        ],
        'sendmail' => [
            'transport' => 'sendmail',
            'path' => env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs'),
        ],
        'mailgun' => [
            'transport' => 'mailgun',
            'domain' => env('MAILGUN_DOMAIN'),
            'PLACEHOLDER_SECRET_VALUEendpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
            'scheme' => 'https',
        ],
    ],
    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'noreply@apsdreamhome.com'),
        'name' => env('MAIL_FROM_NAME', 'APS Dream Home'),
    ],
    'markdown' => [
        'theme' => 'default',
        'paths' => [
            dirname(__DIR__) . '/app/views/emails',
        ],
    ],
];

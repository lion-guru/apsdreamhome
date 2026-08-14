<?php

/**
 * APS Dream Home - Communication Gateway Configuration
 *
 * Central config for ALL communication channels (email, SMS, WhatsApp, push, in-app).
 * Edit per environment (dev uses test mode, prod uses real creds via .env).
 *
 * Each channel has:
 *   - enabled  : bool   Master on/off switch for the channel
 *   - driver   : string Primary provider to use
 *   - fallback : string Auto-failover provider if primary fails
 *
 * Driver values:
 *   email:    'smtp' (PHPMailer via EmailSenderService)
 *   sms:      'twilio' | 'msg91' | 'log'
 *   whatsapp: 'twilio' | 'cloud_api' | 'web'
 *   push:     'webpush' (VAPID via PushSender) | 'fcm' (PushNotificationService)
 *   in_app:   'messaging' (MessagingService)
 */

return [

    'default_channels' => ['email', 'sms', 'whatsapp', 'push', 'in_app'],

    'email' => [
        'enabled'    => true,
        'driver'     => 'smtp',
        'from_email' => $_ENV['SMTP_FROM_EMAIL'] ?? 'notifications@apsdreamhome.com',
        'from_name'  => $_ENV['SMTP_FROM_NAME']  ?? 'APS Dream Home',
    ],

    'sms' => [
        'enabled'  => true,
        'driver'   => $_ENV['SMS_DRIVER']  ?? 'twilio',
        'fallback' => $_ENV['SMS_FALLBACK'] ?? 'msg91',
    ],

    'whatsapp' => [
        'enabled'  => true,
        'driver'   => $_ENV['WHATSAPP_DRIVER']  ?? 'twilio',
        'fallback' => $_ENV['WHATSAPP_FALLBACK'] ?? 'cloud_api',
    ],

    'push' => [
        'enabled'  => true,
        'driver'   => $_ENV['PUSH_DRIVER']  ?? 'webpush',
        'fallback' => $_ENV['PUSH_FALLBACK'] ?? 'fcm',
    ],

    'in_app' => [
        'enabled' => true,
        'driver'  => 'messaging',
    ],

    'retry' => [
        'attempts' => 3,
        'backoff'  => 'exponential',   // or 'linear'
        'base_ms'  => 200,
        'max_ms'   => 5000,
    ],

    'rate_limit' => [
        'email_per_minute'    => 600,
        'sms_per_minute'      => 100,
        'whatsapp_per_minute' => 80,
        'push_per_minute'     => 1000,
    ],
];?>
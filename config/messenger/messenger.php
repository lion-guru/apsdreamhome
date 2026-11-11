<?php

return [
    'messenger' => [
        'transports' => [
            'async' => [
                'dsn' => 'redis://localhost:6379/messages',
                'options' => [
                    'stream' => [
                        'max_connections' => 10,
                    ],
                ],
                'retry_strategy' => [
                    'max_retries' => 3,
                    'multiplier' => 2,
                    'max_delay' => 300,
                    'delay' => 10,
                ],
            ],
            'failed' => 'doctrine://default?queue_name=failed',
            'sync' => 'sync://',
        ],
        
        'routing' => [
            'App\Message\SendNotificationMessage' => 'sync',
            'App\Message\SendEmailMessage' => 'sync',
            'App\Message\SendSmsMessage' => 'sync',
            'App\Message\GeneratePdfMessage' => 'sync',
            'App\Message\GenerateReportMessage' => 'sync',
        ],
        
        'failure_transport' => 'failed',
        
        'serializer' => [
            'default_serializer' => 'messenger.transport.symfony_serializer',
        ],
    ],
];
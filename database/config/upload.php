<?php
return [
    'disks' => [
        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
            'throw' => false,
        ],
        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
        ],
        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
        ],
    ],
    'default' => env('FILESYSTEM_DISK', 'local'),
    'cloud' => env('FILESYSTEM_CLOUD', 's3'),
    'uploads' => [
        'max_size' => env('UPLOAD_MAX_SIZE', 5242880), // 5MB
        'allowed_extensions' => explode(',', env('UPLOAD_ALLOWED_EXTENSIONS', 'jpg,jpeg,png,gif,pdf,doc,docx,xlsx,csv')),
        'image_types' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'document_types' => ['pdf', 'doc', 'docx', 'txt', 'xlsx', 'xls', 'csv'],
        'video_types' => ['mp4', 'avi', 'mov', 'wmv', 'flv'],
        'audio_types' => ['mp3', 'wav', 'ogg', 'aac'],
        'archive_types' => ['zip', 'rar', '7z', 'tar', 'gz'],
        'directories' => [
            'properties' => 'uploads/properties',
            'documents' => 'uploads/documents',
            'avatars' => 'uploads/avatars',
            'temp' => 'uploads/temp',
            'exports' => 'uploads/exports',
        ],
        'naming' => [
            'strategy' => 'timestamp', // timestamp, uuid, hash
            'preserve_original' => false,
            'lowercase' => true,
        ],
        'image_processing' => [
            'enabled' => true,
            'max_width' => 1920,
            'max_height' => 1080,
            'quality' => 85,
            'thumbnails' => [
                'small' => ['width' => 150, 'height' => 150],
                'medium' => ['width' => 400, 'height' => 400],
                'large' => ['width' => 800, 'height' => 600],
            ],
        ],
    ],
];
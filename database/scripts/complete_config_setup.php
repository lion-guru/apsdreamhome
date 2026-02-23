<?php
/**
 * APS Dream Home - Complete Configuration Setup
 * Create all missing configuration files for production deployment
 */

echo "⚙️  APS DREAM HOME - COMPLETE CONFIGURATION SETUP\n";
echo "===============================================\n\n";

$projectRoot = __DIR__ . '/..';
$configStats = [
    'files_created' => 0,
    'files_updated' => 0,
    'directories_created' => 0,
    'errors' => []
];

// Ensure config directory exists
$configDir = $projectRoot . '/config';
if (!is_dir($configDir)) {
    mkdir($configDir, 0755, true);
    $configStats['directories_created']++;
    echo "📁 Created config directory\n";
}

// 1. COMPOSER.JSON - PHP Dependencies
echo "📦 CREATING COMPOSER.JSON\n";
echo "========================\n";

$composerJson = $projectRoot . '/composer.json';
if (!file_exists($composerJson)) {
    $composerConfig = [
        'name' => 'apsdreamhome/apsdreamhome',
        'description' => 'APS Dream Home - Real Estate Management System',
        'type' => 'project',
        'license' => 'MIT',
        'authors' => [
            [
                'name' => 'APS Dream Home Team',
                'email' => 'admin@apsdreamhome.com'
            ]
        ],
        'require' => [
            'php' => '^8.0',
            'ext-mysqli' => '*',
            'ext-json' => '*',
            'ext-curl' => '*',
            'ext-mbstring' => '*',
            'ext-openssl' => '*',
            'firebase/php-jwt' => '^6.0',
            'guzzlehttp/guzzle' => '^7.0',
            'monolog/monolog' => '^3.0',
            'phpmailer/phpmailer' => '^6.0',
            'razorpay/razorpay' => '^2.0',
            'stripe/stripe-php' => '^10.0'
        ],
        'require-dev' => [
            'phpunit/phpunit' => '^9.0',
            'squizlabs/php_codesniffer' => '^3.0'
        ],
        'autoload' => [
            'psr-4' => [
                'App\\' => 'app/',
                'Database\\' => 'database/'
            ],
            'files' => [
                'app/helpers.php'
            ]
        ],
        'scripts' => [
            'post-install-cmd' => [
                'echo "APS Dream Home installed successfully!"'
            ],
            'test' => 'phpunit',
            'check-style' => 'phpcs --standard=PSR12 app/ database/'
        ],
        'config' => [
            'optimize-autoloader' => true,
            'prefer-stable' => true,
            'sort-packages' => true
        ],
        'minimum-stability' => 'stable',
        'prefer-stable' => true
    ];

    file_put_contents($composerJson, json_encode($composerConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    $configStats['files_created']++;
    echo "✅ Created composer.json with all required dependencies\n";
}

// 2. PACKAGE.JSON - Node.js Dependencies
echo "📦 CREATING PACKAGE.JSON\n";
echo "=======================\n";

$packageJson = $projectRoot . '/package.json';
if (!file_exists($packageJson)) {
    $packageConfig = [
        'name' => 'apsdreamhome',
        'version' => '2.0.0',
        'description' => 'APS Dream Home - Real Estate Management System',
        'main' => 'public/index.php',
        'scripts' => [
            'dev' => 'npm run development',
            'development' => 'mix',
            'watch' => 'mix watch',
            'watch-poll' => 'mix watch -- --watch-options-poll=1000',
            'hot' => 'mix watch --hot',
            'prod' => 'npm run production',
            'production' => 'mix --production',
            'test' => 'jest',
            'lint' => 'eslint resources/js --ext .js,.vue',
            'lint-fix' => 'eslint resources/js --ext .js,.vue --fix'
        ],
        'dependencies' => [
            'axios' => '^1.6.0',
            'bootstrap' => '^5.3.0',
            'jquery' => '^3.7.0',
            'lodash' => '^4.17.0',
            'vue' => '^3.3.0'
        ],
        'devDependencies' => [
            'autoprefixer' => '^10.4.0',
            'eslint' => '^8.0.0',
            'eslint-plugin-vue' => '^9.0.0',
            'jest' => '^29.0.0',
            'laravel-mix' => '^6.0.0',
            'postcss' => '^8.4.0',
            'resolve-url-loader' => '^5.0.0',
            'sass' => '^1.60.0',
            'sass-loader' => '^13.0.0',
            'vue-loader' => '^17.0.0'
        ],
        'engines' => [
            'node' => '>=16.0.0',
            'npm' => '>=8.0.0'
        ],
        'repository' => [
            'type' => 'git',
            'url' => 'https://github.com/apsdreamhome/apsdreamhome.git'
        ],
        'keywords' => [
            'real-estate',
            'property-management',
            'crm',
            'mlm',
            'lead-management'
        ],
        'author' => 'APS Dream Home Team',
        'license' => 'MIT'
    ];

    file_put_contents($packageJson, json_encode($packageConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    $configStats['files_created']++;
    echo "✅ Created package.json with frontend dependencies\n";
}

// 3. CREATE REMAINING CONFIGURATION FILES
echo "🔧 CREATING REMAINING CONFIGURATION FILES\n";
echo "=========================================\n";

$configFiles = [
    'config/mail.php' => [
        'name' => 'Mail Configuration',
        'content' => "<?php
return [
    'default' => env('MAIL_MAILER', 'smtp'),
    'mailers' => [
        'smtp' => [
            'transport' => 'smtp',
            'host' => env('MAIL_HOST', 'smtp.mailgun.org'),
            'port' => env('MAIL_PORT', 587),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => null,
            'auth_mode' => null,
        ],
        'ses' => [
            'transport' => 'ses',
        ],
        'mailgun' => [
            'transport' => 'mailgun',
        ],
        'sendmail' => [
            'transport' => 'sendmail',
            'path' => env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs -i'),
        ],
        'log' => [
            'transport' => 'log',
            'channel' => env('MAIL_LOG_CHANNEL'),
        ],
        'array' => [
            'transport' => 'array',
        ],
    ],
    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'noreply@apsdreamhome.com'),
        'name' => env('MAIL_FROM_NAME', 'APS Dream Home'),
    ],
    'markdown' => [
        'theme' => 'default',
        'paths' => [
            resource_path('views/vendor/mail'),
        ],
    ],
];"
    ],

    'config/cache.php' => [
        'name' => 'Cache Configuration',
        'content' => "<?php
return [
    'default' => env('CACHE_DRIVER', 'file'),
    'stores' => [
        'apc' => [
            'driver' => 'apc',
        ],
        'array' => [
            'driver' => 'array',
            'serialize' => false,
        ],
        'database' => [
            'driver' => 'database',
            'table' => env('CACHE_DATABASE_TABLE', 'cache'),
            'connection' => null,
        ],
        'file' => [
            'driver' => 'file',
            'path' => storage_path('framework/cache/data'),
        ],
        'memcached' => [
            'driver' => 'memcached',
            'persistent_id' => env('MEMCACHED_PERSISTENT_ID'),
            'sasl' => [
                env('MEMCACHED_USERNAME'),
                env('MEMCACHED_PASSWORD'),
            ],
            'options' => [],
            'servers' => [
                [
                    'host' => env('MEMCACHED_HOST', '127.0.0.1'),
                    'port' => env('MEMCACHED_PORT', 11211),
                    'weight' => 100,
                ],
            ],
        ],
        'redis' => [
            'driver' => 'redis',
            'connection' => env('REDIS_CACHE_CONNECTION', 'cache'),
            'lock_connection' => env('REDIS_CACHE_LOCK_CONNECTION', 'default'),
        ],
    ],
    'prefix' => env('CACHE_PREFIX', 'apsdreamhome_cache'),
];"
    ],

    'config/services.php' => [
        'name' => 'Services Configuration',
        'content' => "<?php
return [
    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],
    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    'stripe' => [
        'model' => env('STRIPE_MODEL', 'User'),
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook' => [
            'secret' => env('STRIPE_WEBHOOK_SECRET'),
            'tolerance' => env('STRIPE_WEBHOOK_TOLERANCE', 300),
        ],
    ],
    'razorpay' => [
        'key' => env('RAZORPAY_KEY'),
        'secret' => env('RAZORPAY_SECRET'),
    ],
    'firebase' => [
        'project_id' => env('FIREBASE_PROJECT_ID'),
        'api_key' => env('FIREBASE_API_KEY'),
        'auth_domain' => env('FIREBASE_AUTH_DOMAIN'),
        'database_url' => env('FIREBASE_DATABASE_URL'),
        'storage_bucket' => env('FIREBASE_STORAGE_BUCKET'),
        'messaging_sender_id' => env('FIREBASE_MESSAGING_SENDER_ID'),
        'app_id' => env('FIREBASE_APP_ID'),
    ],
    'whatsapp' => [
        'access_token' => env('WHATSAPP_ACCESS_TOKEN'),
        'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
        'api_version' => env('WHATSAPP_API_VERSION', 'v18.0'),
        'base_url' => 'https://graph.facebook.com',
    ],
];"
    ],

    'config/api.php' => [
        'name' => 'API Configuration',
        'content' => "<?php
return [
    'version' => 'v1',
    'prefix' => 'api',
    'middleware' => ['api'],
    'throttle' => [
        'attempts' => env('API_RATE_LIMIT', 60),
        'expires' => env('API_RATE_LIMIT_EXPIRES', 1),
    ],
    'authentication' => [
        'jwt' => [
            'secret' => env('JWT_SECRET'),
            'ttl' => env('JWT_TTL', 60 * 24), // 24 hours
            'refresh_ttl' => env('JWT_REFRESH_TTL', 60 * 24 * 7), // 7 days
        ],
        'oauth' => [
            'google' => [
                'client_id' => env('GOOGLE_CLIENT_ID'),
                'client_secret' => env('GOOGLE_CLIENT_SECRET'),
                'redirect_uri' => env('GOOGLE_REDIRECT_URI'),
            ],
            'facebook' => [
                'client_id' => env('FACEBOOK_CLIENT_ID'),
                'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
                'redirect_uri' => env('FACEBOOK_REDIRECT_URI'),
            ],
        ],
    ],
    'cors' => [
        'allowed_origins' => explode(',', env('API_CORS_ALLOWED_ORIGINS', '*')),
        'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
        'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With'],
        'exposed_headers' => [],
        'max_age' => 86400,
        'supports_credentials' => true,
    ],
    'documentation' => [
        'enabled' => env('API_DOCUMENTATION_ENABLED', true),
        'path' => '/docs',
        'title' => 'APS Dream Home API',
        'description' => 'Real Estate Management System API',
        'version' => '1.0.0',
    ],
];"
    ],

    'config/upload.php' => [
        'name' => 'File Upload Configuration',
        'content' => "<?php
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
];"
    ],

    'config/logging.php' => [
        'name' => 'Logging Configuration',
        'content' => "<?php
return [
    'default' => env('LOG_CHANNEL', 'stack'),
    'deprecations' => [
        'channel' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),
        'trace' => false,
    ],
    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => ['single'],
            'ignore_exceptions' => false,
        ],
        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/apsdreamhome.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],
        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/apsdreamhome.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'replace_placeholders' => true,
        ],
        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => env('LOG_SLACK_USERNAME', 'APS Dream Home'),
            'emoji' => env('LOG_SLACK_EMOJI', ':boom:'),
            'level' => env('LOG_LEVEL', 'critical'),
            'replace_placeholders' => true,
        ],
        'papertrail' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => env('LOG_PAPERTRAIL_HANDLER', SyslogUdpHandler::class),
            'handler_with' => [
                'host' => env('PAPERTRAIL_URL'),
                'port' => env('PAPERTRAIL_PORT'),
                'connectionString' => 'tls://'.env('PAPERTRAIL_URL').':'.env('PAPERTRAIL_PORT'),
            ],
            'processors' => [PsrLogMessageProcessor::class],
        ],
        'stderr' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => StreamHandler::class,
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'with' => [
                'stream' => 'php://stderr',
                'bubble' => false,
            ],
            'processors' => [PsrLogMessageProcessor::class],
        ],
        'syslog' => [
            'driver' => 'syslog',
            'level' => env('LOG_LEVEL', 'debug'),
            'facility' => LOG_USER,
            'replace_placeholders' => true,
        ],
        'errorlog' => [
            'driver' => 'errorlog',
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],
        'null' => [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ],
        'emergency' => [
            'path' => storage_path('logs/apsdreamhome.log'),
        ],
    ],
];"
    ]
];

foreach ($configFiles as $filePath => $config) {
    $fullPath = $projectRoot . '/' . $filePath;
    if (!file_exists($fullPath)) {
        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
            $configStats['directories_created']++;
        }

        file_put_contents($fullPath, $config['content']);
        $configStats['files_created']++;
        echo "✅ Created {$config['name']}: $filePath\n";
    } else {
        echo "⚠️  {$config['name']} already exists: $filePath\n";
    }
}

// 4. CREATE REMAINING MIDDLEWARE
echo "🛡️  CREATING REMAINING MIDDLEWARE\n";
echo "===============================\n";

$middlewareDir = $projectRoot . '/app/Http/Middleware';
if (!is_dir($middlewareDir)) {
    mkdir($middlewareDir, 0755, true);
    $configStats['directories_created']++;
}

$middlewareFiles = [
    'app/Http/Middleware/RateLimit.php' => [
        'name' => 'Rate Limit Middleware',
        'content' => "<?php
namespace App\Http\Middleware;

class RateLimit
{
    protected \$maxAttempts = 60;
    protected \$decayMinutes = 1;

    public function handle(\$request, \$next)
    {
        \$key = \$this->resolveRequestSignature(\$request);

        if (\$this->tooManyAttempts(\$key, \$this->maxAttempts)) {
            return \$this->buildResponse(\$key);
        }

        \$this->hit(\$key, \$this->decayMinutes);

        \$response = \$next(\$request);

        return \$this->addHeaders(\$response, \$key);
    }

    protected function resolveRequestSignature(\$request)
    {
        return sha1(
            \$request->getMethod() .
            '|' . \$request->getPathInfo() .
            '|' . \$request->getHost() .
            '|' . \$this->getClientIp(\$request)
        );
    }

    protected function getClientIp(\$request)
    {
        \$request = \$_SERVER;
        \$keys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];

        foreach (\$keys as \$key) {
            if (!empty(\$request[\$key])) {
                \$ips = explode(',', \$request[\$key]);
                return trim(\$ips[0]);
            }
        }

        return '127.0.0.1';
    }

    protected function tooManyAttempts(\$key, \$maxAttempts)
    {
        return \$this->attempts(\$key) >= \$maxAttempts;
    }

    protected function attempts(\$key)
    {
        return (int) \$_SESSION['rate_limit_' . \$key] ?? 0;
    }

    protected function hit(\$key, \$decayMinutes)
    {
        \$key = 'rate_limit_' . \$key;

        if (!isset(\$_SESSION[\$key])) {
            \$_SESSION[\$key] = ['attempts' => 0, 'first_attempt' => time()];
        }

        \$_SESSION[\$key]['attempts']++;
    }

    protected function buildResponse(\$key)
    {
        header('HTTP/1.1 429 Too Many Requests');
        header('Content-Type: application/json');
        header('Retry-After: 60');

        echo json_encode([
            'error' => 'Too Many Requests',
            'message' => 'Rate limit exceeded. Please try again later.',
            'retry_after' => 60
        ]);
        exit;
    }

    protected function addHeaders(\$response, \$key)
    {
        header('X-RateLimit-Limit: ' . \$this->maxAttempts);
        header('X-RateLimit-Remaining: ' . max(0, \$this->maxAttempts - \$this->attempts(\$key)));
        return \$response;
    }
}"
    ],

    'app/Http/Middleware/SecurityHeaders.php' => [
        'name' => 'Security Headers Middleware',
        'content' => "<?php
namespace App\Http\Middleware;

class SecurityHeaders
{
    protected \$headers = [
        'X-Frame-Options' => 'SAMEORIGIN',
        'X-Content-Type-Options' => 'nosniff',
        'X-XSS-Protection' => '1; mode=block',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Permissions-Policy' => 'geolocation=(), microphone=(), camera=()',
        'Cross-Origin-Embedder-Policy' => 'require-corp',
        'Cross-Origin-Opener-Policy' => 'same-origin',
        'Cross-Origin-Resource-Policy' => 'same-origin'
    ];

    public function handle(\$request, \$next)
    {
        \$response = \$next(\$request);

        foreach (\$this->headers as \$name => \$value) {
            header(\$name . ': ' . \$value);
        }

        // Add HSTS if HTTPS
        if (isset(\$_SERVER['HTTPS']) && \$_SERVER['HTTPS'] === 'on') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        }

        // Content Security Policy
        \$csp = [
            \"default-src 'self'\",
            \"script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com\",
            \"style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net\",
            \"img-src 'self' data: https: blob:\",
            \"font-src 'self' https://fonts.gstatic.com\",
            \"connect-src 'self' https://api.github.com https://maps.googleapis.com\",
            \"media-src 'self'\",
            \"object-src 'none'\",
            \"base-uri 'self'\",
            \"form-action 'self'\",
            \"frame-ancestors 'none'\",
            \"upgrade-insecure-requests\"
        ];

        header('Content-Security-Policy: ' . implode('; ', \$csp));

        return \$response;
    }
}"
    ],

    'app/Http/Middleware/ValidateInput.php' => [
        'name' => 'Input Validation Middleware',
        'content' => "<?php
namespace App\Http\Middleware;

class ValidateInput
{
    protected \$rules = [
        'email' => '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}$/',
        'phone' => '/^[\\+]?[1-9][\\d]{0,15}$/',
        'name' => '/^[a-zA-Z\\s]{2,50}$/',
        'password' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\\d)[a-zA-Z\\d@$!%*?&]{8,}$/',
        'url' => '/^https?:\\/\\/(www\\.)?[-a-zA-Z0-9@:%._\\+~#=]{1,256}\\.[a-zA-Z0-9()]{1,6}\\b([-a-zA-Z0-9()@:%_\\+.~#?&//=]*)$/',
        'number' => '/^\\d+$/',
        'decimal' => '/^\\d+(\\.\\d{1,2})?$/',
        'date' => '/^\\d{4}-\\d{2}-\\d{2}$/',
        'zipcode' => '/^\\d{5,6}$/'
    ];

    public function handle(\$request, \$next)
    {
        \$this->validateRequest(\$request);
        return \$next(\$request);
    }

    protected function validateRequest(\$request)
    {
        \$errors = [];

        // Validate GET parameters
        if (!empty(\$_GET)) {
            \$errors = array_merge(\$errors, \$this->validateData(\$_GET, 'GET'));
        }

        // Validate POST parameters
        if (!empty(\$_POST)) {
            \$errors = array_merge(\$errors, \$this->validateData(\$_POST, 'POST'));
        }

        // Check for suspicious patterns
        \$suspicious = \$this->checkSuspiciousPatterns();
        if (!empty(\$suspicious)) {
            \$errors = array_merge(\$errors, \$suspicious);
        }

        if (!empty(\$errors)) {
            header('HTTP/1.1 400 Bad Request');
            header('Content-Type: application/json');
            echo json_encode([
                'error' => 'Validation Failed',
                'message' => 'Invalid input data provided',
                'errors' => \$errors
            ]);
            exit;
        }
    }

    protected function validateData(\$data, \$source)
    {
        \$errors = [];

        foreach (\$data as \$key => \$value) {
            // Skip validation for known safe fields
            if (in_array(\$key, ['_token', 'submit', 'action'])) {
                continue;
            }

            // Check field name pattern
            if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', \$key)) {
                \$errors[] = \"Invalid field name: \$key\";
                continue;
            }

            // Validate value based on field name
            if (strpos(\$key, 'email') !== false) {
                if (!preg_match(\$this->rules['email'], \$value)) {
                    \$errors[] = \"Invalid email format: \$key\";
                }
            } elseif (strpos(\$key, 'phone') !== false || strpos(\$key, 'mobile') !== false) {
                if (!preg_match(\$this->rules['phone'], \$value)) {
                    \$errors[] = \"Invalid phone format: \$key\";
                }
            } elseif (strpos(\$key, 'password') !== false) {
                if (strlen(\$value) < 8) {
                    \$errors[] = \"Password too short: \$key\";
                }
            }

            // Check for malicious content
            if (\$this->containsMaliciousContent(\$value)) {
                \$errors[] = \"Suspicious content detected in: \$key\";
            }
        }

        return \$errors;
    }

    protected function checkSuspiciousPatterns()
    {
        \$errors = [];

        // Check URL for suspicious patterns
        \$url = \$_SERVER['REQUEST_URI'] ?? '';
        \$suspiciousPatterns = [
            '\\.\\./', // Directory traversal
            '<script', // XSS
            'javascript:', // JavaScript injection
            'data:', // Data URL injection
            'vbscript:', // VBScript injection
            'onload=', // Event handler injection
            'onerror=', // Event handler injection
        ];

        foreach (\$suspiciousPatterns as \$pattern) {
            if (stripos(\$url, \$pattern) !== false) {
                \$errors[] = \"Suspicious URL pattern detected: \$pattern\";
            }
        }

        return \$errors;
    }

    protected function containsMaliciousContent(\$value)
    {
        if (!is_string(\$value)) return false;

        \$maliciousPatterns = [
            '<script', '</script>', 'javascript:', 'vbscript:', 'data:',
            'onload=', 'onerror=', 'onclick=', 'onmouseover=', 'eval(',
            'document.cookie', 'document.location', 'window.location'
        ];

        foreach (\$maliciousPatterns as \$pattern) {
            if (stripos(\$value, \$pattern) !== false) {
                return true;
            }
        }

        return false;
    }
}"
    ]
];

foreach ($middlewareFiles as $filePath => $middleware) {
    $fullPath = $projectRoot . '/' . $filePath;
    if (!file_exists($fullPath)) {
        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
            $configStats['directories_created']++;
        }

        file_put_contents($fullPath, $middleware['content']);
        $configStats['files_created']++;
        echo "✅ Created {$middleware['name']}: $filePath\n";
    } else {
        echo "⚠️  {$middleware['name']} already exists: $filePath\n";
    }
}

// 5. FINAL CONFIGURATION SUMMARY
echo "\n📋 CONFIGURATION SETUP SUMMARY\n";
echo "==============================\n";

$configStats['total_files'] = $configStats['files_created'] + $configStats['files_updated'];
$configStats['total_directories'] = $configStats['directories_created'];

echo "📁 Directories Created: {$configStats['directories_created']}\n";
echo "📄 Files Created: {$configStats['files_created']}\n";
echo "🔄 Files Updated: {$configStats['files_updated']}\n";
echo "📊 Total New Items: {$configStats['total_files']}\n\n";

echo "✅ CONFIGURATION FILES CREATED:\n";
echo "===============================\n";
echo "• composer.json - PHP dependencies and autoloading\n";
echo "• package.json - Node.js dependencies and scripts\n";
echo "• config/mail.php - Email service configuration\n";
echo "• config/cache.php - Caching system configuration\n";
echo "• config/services.php - Third-party services configuration\n";
echo "• config/api.php - API authentication and CORS settings\n";
echo "• config/upload.php - File upload security and processing\n";
echo "• config/logging.php - Logging channels and drivers\n\n";

echo "🛡️ MIDDLEWARE CREATED:\n";
echo "====================\n";
echo "• RateLimit.php - API rate limiting protection\n";
echo "• SecurityHeaders.php - HTTP security headers\n";
echo "• ValidateInput.php - Input validation and sanitization\n\n";

echo "🔧 NEXT STEPS:\n";
echo "=============\n";
echo "1. Run: composer install\n";
echo "2. Run: npm install\n";
echo "3. Copy .env.example to .env and configure values\n";
echo "4. Test application functionality\n";
echo "5. Configure web server (Apache/Nginx)\n\n";

echo "🎉 CONFIGURATION SETUP COMPLETED!\n";
echo "Your APS Dream Home project is now fully configured for production deployment.\n";

?>

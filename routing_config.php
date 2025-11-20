<?php
/**
 * Routing Configuration
 * APS Dream Home - Enhanced Routing System
 * 
 * Configuration for the modern routing system
 */

return [
    // Routing system settings
    'routing' => [
        // Enable modern routing system
        'enabled' => true,
        
        // Default routing system: 'modern', 'legacy', 'hybrid'
        'default_system' => 'hybrid',
        
        // Route loading order
        'load_order' => ['modern', 'web', 'legacy'],
        
        // Enable route caching
        'cache_enabled' => false,
        'cache_path' => __DIR__ . '/cache/routes/',
        
        // Enable route debugging
        'debug' => true,
        
        // Route timeout (seconds)
        'timeout' => 30,
    ],
    
    // Middleware settings
    'middleware' => [
        // Authentication middleware
        'auth' => [
            'enabled' => true,
            'redirect_to' => '/login',
            'session_key' => 'user_id',
            'remember_me' => true,
            'remember_me_duration' => 2592000, // 30 days
        ],
        
        // Admin middleware
        'admin' => [
            'enabled' => true,
            'role_key' => 'user_role',
            'required_role' => 'admin',
            'redirect_to' => '/unauthorized',
        ],
        
        // API middleware
        'api' => [
            'enabled' => true,
            'require_key' => true,
            'key_header' => 'X-API-Key',
            'key_param' => 'api_key',
            'rate_limit' => [
                'enabled' => true,
                'max_requests' => 100,
                'window' => 3600, // 1 hour
            ],
        ],
        
        // CSRF protection
        'csrf' => [
            'enabled' => true,
            'token_name' => 'csrf_token',
            'token_length' => 32,
            'expire_time' => 3600, // 1 hour
        ],
        
        // Rate limiting
        'rate_limit' => [
            'enabled' => true,
            'max_requests' => 1000,
            'window' => 3600, // 1 hour
            'excluded_paths' => ['/login', '/register', '/api/auth'],
        ],
    ],
    
    // Route groups
    'route_groups' => [
        'public' => [
            'prefix' => '',
            'middleware' => [],
            'namespace' => 'App\\Controllers\\Public\\',
        ],
        
        'user' => [
            'prefix' => 'user',
            'middleware' => ['auth'],
            'namespace' => 'App\\Controllers\\User\\',
        ],
        
        'admin' => [
            'prefix' => 'admin',
            'middleware' => ['auth', 'admin'],
            'namespace' => 'App\\Controllers\\Admin\\',
        ],
        
        'api' => [
            'prefix' => 'api',
            'middleware' => ['api'],
            'namespace' => 'App\\Controllers\\Api\\',
        ],
        
        'auth' => [
            'prefix' => 'auth',
            'middleware' => [],
            'namespace' => 'App\\Controllers\\Auth\\',
        ],
    ],
    
    // Route aliases
    'aliases' => [
        'home' => '/',
        'index' => '/',
        'homepage' => '/',
        'main' => '/',
        
        // User dashboard aliases
        'dashboard' => '/user/dashboard',
        'profile' => '/user/profile',
        'properties' => '/user/properties',
        'bookings' => '/user/bookings',
        'favorites' => '/user/favorites',
        'messages' => '/user/messages',
        'notifications' => '/user/notifications',
        'settings' => '/user/settings',
        
        // Admin dashboard aliases
        'admin-dashboard' => '/admin/dashboard',
        'admin-users' => '/admin/users',
        'admin-properties' => '/admin/properties',
        'admin-bookings' => '/admin/bookings',
        'admin-reports' => '/admin/reports',
        'admin-settings' => '/admin/settings',
        'admin-categories' => '/admin/categories',
        'admin-locations' => '/admin/locations',
        'admin-system' => '/admin/system-status',
        
        // Property aliases
        'property-search' => '/properties',
        'property-list' => '/properties',
        'property-details' => '/property/{id}',
        'project-search' => '/projects',
        'project-list' => '/projects',
        'project-details' => '/project/{id}',
        
        // Service aliases
        'services' => '/services',
        'legal-services' => '/services/legal',
        'financial-services' => '/services/financial',
        'interior-design' => '/services/interior',
        
        // Blog aliases
        'blog' => '/blog',
        'blog-post' => '/blog/{slug}',
        'blog-category' => '/blog/category/{category}',
        'blog-author' => '/blog/author/{author}',
        
        // Search aliases
        'search' => '/search',
        'advanced-search' => '/search/advanced',
        'property-search' => '/search/properties',
        'project-search' => '/search/projects',
        'blog-search' => '/search/blog',
        
        // Contact and support
        'contact' => '/contact',
        'contact-us' => '/contact',
        'support' => '/support',
        'help' => '/support',
        'faq' => '/faq',
        'privacy-policy' => '/privacy-policy',
        'terms-of-service' => '/terms-of-service',
        'sitemap' => '/sitemap',
        
        // Authentication aliases
        'login' => '/auth/login',
        'register' => '/auth/register',
        'logout' => '/auth/logout',
        'forgot-password' => '/auth/forgot-password',
        'reset-password' => '/auth/reset-password',
        'verify-email' => '/auth/verify-email',
        
        // Utility aliases
        'coming-soon' => '/coming-soon',
        'maintenance' => '/maintenance',
        'thank-you' => '/thank-you',
        '404' => '/404',
        'error' => '/error',
        'unauthorized' => '/unauthorized',
        'forbidden' => '/forbidden',
    ],
    
    // Error handling
    'errors' => [
        '404' => [
            'handler' => 'errors/404.php',
            'template' => 'errors/404_template.php',
        ],
        '403' => [
            'handler' => 'errors/403.php',
            'template' => 'errors/403_template.php',
        ],
        '500' => [
            'handler' => 'errors/500.php',
            'template' => 'errors/500_template.php',
        ],
        '503' => [
            'handler' => 'errors/503.php',
            'template' => 'errors/503_template.php',
        ],
    ],
    
    // Route caching
    'cache' => [
        'enabled' => false,
        'driver' => 'file', // file, redis, memcached
        'ttl' => 3600, // 1 hour
        'prefix' => 'route_cache_',
        'path' => __DIR__ . '/cache/routes/',
    ],
    
    // Performance settings
    'performance' => [
        'enable_opcache' => true,
        'enable_apcu' => true,
        'minify_routes' => false,
        'compress_output' => true,
        'cache_headers' => [
            'Cache-Control' => 'public, max-age=3600',
            'Expires' => '3600',
        ],
    ],
    
    // Development settings
    'development' => [
        'show_errors' => true,
        'log_errors' => true,
        'error_level' => 'debug',
        'profiling' => false,
        'debug_bar' => false,
    ],
    
    // Production settings
    'production' => [
        'show_errors' => false,
        'log_errors' => true,
        'error_level' => 'error',
        'profiling' => false,
        'debug_bar' => false,
    ],
];
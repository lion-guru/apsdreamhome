<?php
/**
 * Production Environment Configuration
 * For live domain deployment
 */

return [
    // Database Configuration (UPDATE THESE for live server)
    'DB_HOST' => getenv('DB_HOST') ?: 'localhost',
    'DB_PORT' => getenv('DB_PORT') ?: 3306,
    'DB_NAME' => getenv('DB_NAME') ?: 'apsdreamhome',
    'DB_USER' => getenv('DB_USER') ?: 'aps_user',  // NOT root - create separate user
    'DB_PASS' => getenv('DB_PASS') ?: '',  // Strong password required
    
    // Application Settings
    'APP_ENV' => 'production',
    'APP_DEBUG' => false,  // NEVER true on production
    'APP_URL' => 'https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost'),
    'APP_NAME' => 'APS Dream Home',
    
    // Security Settings
    'CSRF_PROTECTION' => true,
    'SESSION_TIMEOUT' => 3600,  // 1 hour
    'SESSION_LIFETIME' => 7200,  // 2 hours
    'PASSWORD_MIN_LENGTH' => 8,
    'MAX_LOGIN_ATTEMPTS' => 5,
    
    // SSL/HTTPS Settings
    'FORCE_HTTPS' => true,  // Redirect HTTP to HTTPS
    'SECURE_COOKIES' => true,  // Cookies only over HTTPS
    
    // Email Configuration (Update with your SMTP provider)
    'SMTP_HOST' => getenv('SMTP_HOST') ?: 'smtp.gmail.com',
    'SMTP_PORT' => getenv('SMTP_PORT') ?: 587,
    'SMTP_USER' => getenv('SMTP_USER') ?: 'noreply@apsdreamhome.com',
    'SMTP_PASS' => getenv('SMTP_PASS') ?: '',  // App password
    'SMTP_FROM' => 'APS Dream Home <noreply@apsdreamhome.com>',
    
    // File Upload Settings
    'MAX_UPLOAD_SIZE' => 10 * 1024 * 1024,  // 10MB
    'ALLOWED_IMAGE_TYPES' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
    'ALLOWED_DOC_TYPES' => ['pdf', 'doc', 'docx'],
    
    // AI/Gemini API Settings
    'GEMINI_API_KEY' => getenv('GEMINI_API_KEY') ?: '',
    'GEMINI_MODEL' => 'gemini-pro',
    
    // Google OAuth (Update for your domain)
    'GOOGLE_CLIENT_ID' => getenv('GOOGLE_CLIENT_ID') ?: '',
    'GOOGLE_CLIENT_SECRET' => getenv('GOOGLE_CLIENT_SECRET') ?: '',
    'GOOGLE_REDIRECT_URI' => 'https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/google_callback.php',
    
    // Cache Settings
    'CACHE_ENABLED' => true,
    'CACHE_TTL' => 3600,  // 1 hour
    
    // Logging
    'LOG_LEVEL' => 'error',  // debug, info, warning, error
    'LOG_FILE' => __DIR__ . '/../../logs/production.log',
    
    // Maintenance Mode
    'MAINTENANCE_MODE' => false,
    'MAINTENANCE_MESSAGE' => 'Site is under maintenance. Please check back later.',
];

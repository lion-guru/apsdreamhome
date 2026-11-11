<?php
/**
 * PHPStan bootstrap.
 *
 * WHY THIS FILE EXISTS (S453)
 * ---------------------------
 * phpstan.neon never referenced a bootstrap file, so PHPStan analysed the
 * codebase WITHOUT the application's constants.  Every `BASE_URL`, `APP_ROOT`,
 * `STORAGE_PATH`, `VIEW_PATH` ... reference in a view or controller was
 * therefore reported as "Constant X not found" - 1442 errors on their own
 * (BASE_URL alone: 1336).
 *
 * The real config/bootstrap.php cannot be used as a PHPStan bootstrap: it
 * opens a database connection, starts a session and loads the whole
 * autoloader, which would make static analysis non-deterministic and slow.
 *
 * So this file mirrors EXACTLY the constant block of config/bootstrap.php
 * (lines 11-32 + BASE_URL) and nothing else.  Same names, same values,
 * no side effects.
 *
 * If a constant is added to config/bootstrap.php, add it here too.
 */

if (!defined('PHPSTAN')) {
    define('PHPSTAN', true);
}

// ── mirrors config/bootstrap.php L11-32 ─────────────────────────────────────
if (!defined('APP_NAME'))     define('APP_NAME', 'APSDreamHome');
if (!defined('APP_VERSION'))  define('APP_VERSION', '1.0.0');
if (!defined('ENVIRONMENT'))  define('ENVIRONMENT', 'development');

if (!defined('APP_ROOT'))     define('APP_ROOT', dirname(__DIR__));
if (!defined('CONFIG_PATH'))  define('CONFIG_PATH', APP_ROOT . '/config');
if (!defined('APP_PATH'))     define('APP_PATH', APP_ROOT . '/app');
if (!defined('PUBLIC_PATH'))  define('PUBLIC_PATH', APP_ROOT . '/public');
if (!defined('STORAGE_PATH')) define('STORAGE_PATH', APP_ROOT . '/storage');
if (!defined('VIEW_PATH'))    define('VIEW_PATH', APP_PATH . '/views');
if (!defined('CORE_PATH'))    define('CORE_PATH', APP_PATH . '/core');
if (!defined('CONTROLLER_PATH')) define('CONTROLLER_PATH', APP_PATH . '/controllers');
if (!defined('MODEL_PATH'))   define('MODEL_PATH', APP_PATH . '/models');
if (!defined('HELPER_PATH'))  define('HELPER_PATH', APP_PATH . '/helpers');
if (!defined('ROUTE_PATH'))   define('ROUTE_PATH', APP_PATH . '/routes');
if (!defined('SERVICE_PATH')) define('SERVICE_PATH', APP_PATH . '/services');
if (!defined('MIDDLEWARE_PATH')) define('MIDDLEWARE_PATH', APP_PATH . '/middleware');
if (!defined('CACHE_PATH'))   define('CACHE_PATH', STORAGE_PATH . '/cache');
if (!defined('LOG_PATH'))     define('LOG_PATH', STORAGE_PATH . '/logs');
if (!defined('SESSION_PATH')) define('SESSION_PATH', STORAGE_PATH . '/sessions');
if (!defined('UPLOAD_PATH'))  define('UPLOAD_PATH', PUBLIC_PATH . '/uploads');
if (!defined('APS_ROOT'))     define('APS_ROOT', APP_ROOT);

// ── mirrors config/bootstrap.php L82 (the CLI / no-host fallback) ───────────
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/apsdreamhome');
}

// ── mirrors config/bootstrap.php L86 / L92 ─────────────────────────────────
if (!defined('WHATSAPP_SERVICE_URL')) {
    define('WHATSAPP_SERVICE_URL', getenv('WHATSAPP_SERVICE_URL') ?: 'http://localhost:3001');
}
if (!defined('APP_ENV')) {
    define('APP_ENV', ENVIRONMENT);
}

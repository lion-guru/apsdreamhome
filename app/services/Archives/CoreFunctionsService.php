<?php

/**
 * Core Functions Management Routes
 */

// Logging and monitoring routes
$router->post('/api/core-functions/log-admin-action', 'CoreFunctionsControllerNew@logAdminAction');

// Validation routes
$router->post('/api/core-functions/validate-input', 'CoreFunctionsControllerNew@validateInput');
$router->post('/api/core-functions/validate-request-headers', 'CoreFunctionsControllerNew@validateRequestHeaders');

// Security and session routes
$router->post('/api/core-functions/send-security-response', 'CoreFunctionsControllerNew@sendSecurityResponse');
$router->post('/api/core-functions/init-admin-session', 'CoreFunctionsControllerNew@initAdminSession');

// URL and file handling routes
$router->get('/api/core-functions/get-current-url', 'CoreFunctionsControllerNew@getCurrentUrl');
$router->post('/api/core-functions/check-file-exists', 'CoreFunctionsControllerNew@checkFileExists');
$router->post('/api/core-functions/safe-redirect', 'CoreFunctionsControllerNew@safeRedirect');

// Data formatting routes
$router->post('/api/core-functions/format-phone-number', 'CoreFunctionsControllerNew@formatPhoneNumber');
$router->post('/api/core-functions/generate-random-string', 'CoreFunctionsControllerNew@generateRandomString');
$router->post('/api/core-functions/format-currency', 'CoreFunctionsControllerNew@formatCurrency');
$router->post('/api/core-functions/format-date', 'CoreFunctionsControllerNew@formatDate');
$router->post('/api/core-functions/generate-slug', 'CoreFunctionsControllerNew@generateSlug');
$router->post('/api/core-functions/truncate-text', 'CoreFunctionsControllerNew@truncateText');

// Authentication and authorization routes
$router->get('/api/core-functions/check-authentication', 'CoreFunctionsControllerNew@checkAuthentication');
$router->post('/api/core-functions/check-permission', 'CoreFunctionsControllerNew@checkPermission');

// File and directory operations
$router->post('/api/core-functions/sanitize-filename', 'CoreFunctionsControllerNew@sanitizeFilename');
$router->post('/api/core-functions/ensure-directory-exists', 'CoreFunctionsControllerNew@ensureDirectoryExists');
$router->post('/api/core-functions/get-file-extension', 'CoreFunctionsControllerNew@getFileExtension');
$router->post('/api/core-functions/resize-image', 'CoreFunctionsControllerNew@resizeImage');

// Request and response handling
$router->get('/api/core-functions/get-client-ip', 'CoreFunctionsControllerNew@getClientIp');
$router->post('/api/core-functions/check-rate-limit', 'CoreFunctionsControllerNew@checkRateLimit');
$router->post('/api/core-functions/send-json-response', 'CoreFunctionsControllerNew@sendJsonResponse');
$router->get('/api/core-functions/check-ajax-request', 'CoreFunctionsControllerNew@checkAjaxRequest');

// External integrations
$router->get('/api/core-functions/get-whatsapp-templates', 'CoreFunctionsControllerNew@getWhatsAppTemplates');

// Password handling
$router->post('/api/core-functions/hash-password', 'CoreFunctionsControllerNew@hashPassword');
$router->post('/api/core-functions/verify-password-hash', 'CoreFunctionsControllerNew@verifyPasswordHash');

<?php

use App\Http\Controllers\CoreFunctionsControllerNew;
use Illuminate\Support\Facades\Route;

/**
 * Core Functions Management Routes
 */

Route::prefix('core-functions')->group(function () {
    
    // Logging and monitoring routes
    Route::post('/log-admin-action', [CoreFunctionsControllerNew::class, 'logAdminAction'])
        ->name('core-functions.log-admin-action');

    // Validation routes
    Route::post('/validate-input', [CoreFunctionsControllerNew::class, 'validateInput'])
        ->name('core-functions.validate-input');
    
    Route::post('/validate-request-headers', [CoreFunctionsControllerNew::class, 'validateRequestHeaders'])
        ->name('core-functions.validate-request-headers');

    // Security and session routes
    Route::post('/send-security-response', [CoreFunctionsControllerNew::class, 'sendSecurityResponse'])
        ->name('core-functions.send-security-response');
    
    Route::post('/init-admin-session', [CoreFunctionsControllerNew::class, 'initAdminSession'])
        ->name('core-functions.init-admin-session');

    // URL and file handling routes
    Route::get('/get-current-url', [CoreFunctionsControllerNew::class, 'getCurrentUrl'])
        ->name('core-functions.get-current-url');
    
    Route::post('/check-file-exists', [CoreFunctionsControllerNew::class, 'checkFileExists'])
        ->name('core-functions.check-file-exists');
    
    Route::post('/safe-redirect', [CoreFunctionsControllerNew::class, 'safeRedirect'])
        ->name('core-functions.safe-redirect');

    // Data formatting routes
    Route::post('/format-phone-number', [CoreFunctionsControllerNew::class, 'formatPhoneNumber'])
        ->name('core-functions.format-phone-number');
    
    Route::post('/generate-random-string', [CoreFunctionsControllerNew::class, 'generateRandomString'])
        ->name('core-functions.generate-random-string');
    
    Route::post('/format-currency', [CoreFunctionsControllerNew::class, 'formatCurrency'])
        ->name('core-functions.format-currency');
    
    Route::post('/format-date', [CoreFunctionsControllerNew::class, 'formatDate'])
        ->name('core-functions.format-date');
    
    Route::post('/generate-slug', [CoreFunctionsControllerNew::class, 'generateSlug'])
        ->name('core-functions.generate-slug');
    
    Route::post('/truncate-text', [CoreFunctionsControllerNew::class, 'truncateText'])
        ->name('core-functions.truncate-text');

    // Authentication and authorization routes
    Route::get('/check-authentication', [CoreFunctionsControllerNew::class, 'checkAuthentication'])
        ->name('core-functions.check-authentication');
    
    Route::post('/check-permission', [CoreFunctionsControllerNew::class, 'checkPermission'])
        ->name('core-functions.check-permission');

    // File and directory operations
    Route::post('/sanitize-filename', [CoreFunctionsControllerNew::class, 'sanitizeFilename'])
        ->name('core-functions.sanitize-filename');
    
    Route::post('/ensure-directory-exists', [CoreFunctionsControllerNew::class, 'ensureDirectoryExists'])
        ->name('core-functions.ensure-directory-exists');
    
    Route::post('/get-file-extension', [CoreFunctionsControllerNew::class, 'getFileExtension'])
        ->name('core-functions.get-file-extension');
    
    Route::post('/resize-image', [CoreFunctionsControllerNew::class, 'resizeImage'])
        ->name('core-functions.resize-image');

    // Request and response handling
    Route::get('/get-client-ip', [CoreFunctionsControllerNew::class, 'getClientIp'])
        ->name('core-functions.get-client-ip');
    
    Route::post('/check-rate-limit', [CoreFunctionsControllerNew::class, 'checkRateLimit'])
        ->name('core-functions.check-rate-limit');
    
    Route::post('/send-json-response', [CoreFunctionsControllerNew::class, 'sendJsonResponse'])
        ->name('core-functions.send-json-response');
    
    Route::get('/check-ajax-request', [CoreFunctionsControllerNew::class, 'checkAjaxRequest'])
        ->name('core-functions.check-ajax-request');

    // External integrations
    Route::get('/get-whatsapp-templates', [CoreFunctionsControllerNew::class, 'getWhatsAppTemplates'])
        ->name('core-functions.get-whatsapp-templates');

    // Password handling
    Route::post('/hash-password', [CoreFunctionsControllerNew::class, 'hashPassword'])
        ->name('core-functions.hash-password');
    
    Route::post('/verify-password-hash', [CoreFunctionsControllerNew::class, 'verifyPasswordHash'])
        ->name('core-functions.verify-password-hash');
});

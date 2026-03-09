<?php

use App\Http\Controllers\RequestMiddlewareController;
use Illuminate\Support\Facades\Route;

/**
 * Request Middleware Routes
 */

Route::prefix('request-middleware')->group(function () {
    
    // Request information routes
    Route::get('/metadata', [RequestMiddlewareController::class, 'getRequestMetadata'])
        ->name('request-middleware.metadata');
    
    Route::get('/client-info', [RequestMiddlewareController::class, 'getClientInfo'])
        ->name('request-middleware.client-info');

    // Security and validation routes
    Route::get('/detect-suspicious', [RequestMiddlewareController::class, 'detectSuspiciousActivity'])
        ->name('request-middleware.detect-suspicious');
    
    Route::post('/validate-json', [RequestMiddlewareController::class, 'validateJsonRequest'])
        ->name('request-middleware.validate-json');
    
    Route::post('/sanitize-input', [RequestMiddlewareController::class, 'sanitizeInput'])
        ->name('request-middleware.sanitize-input');

    // Middleware management routes
    Route::get('/stats', [RequestMiddlewareController::class, 'getMiddlewareStats'])
        ->name('request-middleware.stats');
    
    Route::get('/available', [RequestMiddlewareController::class, 'getAvailableMiddleware'])
        ->name('request-middleware.available');
    
    Route::post('/register', [RequestMiddlewareController::class, 'registerMiddleware'])
        ->name('request-middleware.register');
    
    Route::post('/apply', [RequestMiddlewareController::class, 'applyMiddleware'])
        ->name('request-middleware.apply');

    // Testing route
    Route::get('/test', [RequestMiddlewareController::class, 'testMiddleware'])
        ->name('request-middleware.test');
});

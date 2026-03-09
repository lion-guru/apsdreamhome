<?php

use App\Http\Controllers\SecurityController;
use Illuminate\Support\Facades\Route;

/**
 * Security Management Routes
 */

Route::prefix('security')->group(function () {
    
    // Security testing routes
    Route::get('/run-tests', [SecurityController::class, 'runTests'])
        ->name('security.run-tests');
    
    Route::get('/score', [SecurityController::class, 'getScore'])
        ->name('security.score');
    
    Route::post('/generate-report', [SecurityController::class, 'generateReport'])
        ->name('security.generate-report');
    
    Route::get('/download-report/{filename}', [SecurityController::class, 'downloadReport'])
        ->name('security.download-report');

    // Security validation routes
    Route::post('/validate-input', [SecurityController::class, 'validateInput'])
        ->name('security.validate-input');
    
    Route::post('/hash-password', [SecurityController::class, 'hashPassword'])
        ->name('security.hash-password');
    
    Route::post('/verify-password', [SecurityController::class, 'verifyPassword'])
        ->name('security.verify-password');
    
    Route::get('/csrf-token', [SecurityController::class, 'generateCsrfToken'])
        ->name('security.csrf-token');
    
    Route::post('/validate-csrf', [SecurityController::class, 'validateCsrfToken'])
        ->name('security.validate-csrf');

    // Security monitoring routes
    Route::post('/check-rate-limit', [SecurityController::class, 'checkRateLimit'])
        ->name('security.check-rate-limit');
    
    Route::post('/detect-suspicious', [SecurityController::class, 'detectSuspiciousActivity'])
        ->name('security.detect-suspicious');
    
    Route::post('/log-event', [SecurityController::class, 'logSecurityEvent'])
        ->name('security.log-event');

    // Security dashboard and recommendations
    Route::get('/dashboard', [SecurityController::class, 'getDashboard'])
        ->name('security.dashboard');
    
    Route::get('/recommendations', [SecurityController::class, 'getRecommendations'])
        ->name('security.recommendations');

    // Component testing
    Route::post('/test-component', [SecurityController::class, 'testComponent'])
        ->name('security.test-component');
});

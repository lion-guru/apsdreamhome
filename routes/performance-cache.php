<?php

use App\Http\Controllers\PerformanceCacheController;
use Illuminate\Support\Facades\Route;

/**
 * Performance Cache Management Routes
 */

Route::prefix('performance-cache')->group(function () {
    
    // Basic cache operations
    Route::post('/set', [PerformanceCacheController::class, 'set'])
        ->name('performance-cache.set');
    
    Route::get('/get', [PerformanceCacheController::class, 'get'])
        ->name('performance-cache.get');
    
    Route::delete('/delete', [PerformanceCacheController::class, 'delete'])
        ->name('performance-cache.delete');
    
    Route::delete('/clear', [PerformanceCacheController::class, 'clear'])
        ->name('performance-cache.clear');

    // Advanced cache operations
    Route::post('/remember', [PerformanceCacheController::class, 'remember'])
        ->name('performance-cache.remember');
    
    Route::post('/memoize', [PerformanceCacheController::class, 'memoize'])
        ->name('performance-cache.memoize');
    
    Route::post('/cache-query', [PerformanceCacheController::class, 'cacheQuery'])
        ->name('performance-cache.cache-query');
    
    Route::post('/cache-api-response', [PerformanceCacheController::class, 'cacheApiResponse'])
        ->name('performance-cache.cache-api-response');
    
    Route::post('/cache-computed', [PerformanceCacheController::class, 'cacheComputed'])
        ->name('performance-cache.cache-computed');

    // Tag-based operations
    Route::delete('/clear-by-tags', [PerformanceCacheController::class, 'clearByTags'])
        ->name('performance-cache.clear-by-tags');

    // Cache management and monitoring
    Route::get('/stats', [PerformanceCacheController::class, 'getStats'])
        ->name('performance-cache.stats');
    
    Route::post('/reset-stats', [PerformanceCacheController::class, 'resetStats'])
        ->name('performance-cache.reset-stats');
    
    Route::get('/info', [PerformanceCacheController::class, 'getCacheInfo'])
        ->name('performance-cache.info');
    
    Route::get('/size', [PerformanceCacheController::class, 'getCacheSize'])
        ->name('performance-cache.size');
    
    Route::post('/optimize', [PerformanceCacheController::class, 'optimize'])
        ->name('performance-cache.optimize');
    
    Route::post('/warmup', [PerformanceCacheController::class, 'warmUp'])
        ->name('performance-cache.warmup');
    
    Route::get('/report', [PerformanceCacheController::class, 'generateReport'])
        ->name('performance-cache.report');
    
    Route::get('/dashboard', [PerformanceCacheController::class, 'getDashboard'])
        ->name('performance-cache.dashboard');
});

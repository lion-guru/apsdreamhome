<?php

use App\Http\Controllers\EventControllerNew;
use Illuminate\Support\Facades\Route;

/**
 * Event Bus Management Routes
 */

Route::prefix('events')->group(function () {
    
    // Event subscription routes
    Route::post('/subscribe', [EventControllerNew::class, 'subscribe'])
        ->name('events.subscribe');
    
    Route::post('/subscribe-wildcard', [EventControllerNew::class, 'subscribeWildcard'])
        ->name('events.subscribe-wildcard');
    
    Route::delete('/unsubscribe', [EventControllerNew::class, 'unsubscribe'])
        ->name('events.unsubscribe');
    
    Route::delete('/unsubscribe-wildcard', [EventControllerNew::class, 'unsubscribeWildcard'])
        ->name('events.unsubscribe-wildcard');

    // Event publishing routes
    Route::post('/publish', [EventControllerNew::class, 'publish'])
        ->name('events.publish');

    // Event transformation and middleware routes
    Route::post('/add-transformer', [EventControllerNew::class, 'addTransformer'])
        ->name('events.add-transformer');
    
    Route::post('/add-middleware', [EventControllerNew::class, 'addMiddleware'])
        ->name('events.add-middleware');

    // Event management routes
    Route::get('/history', [EventControllerNew::class, 'getHistory'])
        ->name('events.history');
    
    Route::delete('/clear-history', [EventControllerNew::class, 'clearHistory'])
        ->name('events.clear-history');
    
    Route::get('/subscriptions', [EventControllerNew::class, 'getSubscriptions'])
        ->name('events.subscriptions');

    // Event reporting and analytics routes
    Route::get('/report', [EventControllerNew::class, 'generateReport'])
        ->name('events.report');
    
    Route::get('/dashboard', [EventControllerNew::class, 'getDashboard'])
        ->name('events.dashboard');
    
    Route::get('/statistics', [EventControllerNew::class, 'getStatistics'])
        ->name('events.statistics');

    // Event demonstration route
    Route::post('/demonstrate', [EventControllerNew::class, 'demonstrate'])
        ->name('events.demonstrate');
});

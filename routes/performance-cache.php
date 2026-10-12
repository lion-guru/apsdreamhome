<?php

use App\Http\Controllers\PerformanceCacheController;

/**
 * Performance Cache Management Routes
 *
 * NOTE: All routes require a valid Bearer API token (ApiAuthMiddleware).
 * Arbitrary cache set/flush must never be served anonymously (cache-poisoning risk).
 */

// Basic cache operations
$router->post('/api/performance-cache/set', 'PerformanceCacheController@cacheSet')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/performance-cache/get', 'PerformanceCacheController@cacheGet')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->delete('/api/performance-cache/delete', 'PerformanceCacheController@cacheForget')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->delete('/api/performance-cache/clear', 'PerformanceCacheController@cacheFlush')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// Cache stats and monitoring
$router->get('/api/performance-cache/stats', 'PerformanceCacheController@cacheStats')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/performance-cache/dashboard', 'PerformanceCacheController@dashboardStats')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/performance-cache/invalidate', 'PerformanceCacheController@invalidateDashboard')->middleware('App\Http\Middleware\ApiAuthMiddleware');

<?php

use App\Http\Controllers\PerformanceCacheController;

/**
 * Performance Cache Management Routes
 */

// Basic cache operations
$router->post('/api/performance-cache/set', 'PerformanceCacheController@cacheSet');
$router->get('/api/performance-cache/get', 'PerformanceCacheController@cacheGet');
$router->delete('/api/performance-cache/delete', 'PerformanceCacheController@cacheForget');
$router->delete('/api/performance-cache/clear', 'PerformanceCacheController@cacheFlush');

// Cache stats and monitoring
$router->get('/api/performance-cache/stats', 'PerformanceCacheController@cacheStats');
$router->get('/api/performance-cache/dashboard', 'PerformanceCacheController@dashboardStats');
$router->post('/api/performance-cache/invalidate', 'PerformanceCacheController@invalidateDashboard');
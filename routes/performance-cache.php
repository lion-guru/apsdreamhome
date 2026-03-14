<?php

use App\Http\Controllers\PerformanceCacheController;
use Illuminate\Support\Facades\Route;

/**
 * Performance Cache Management Routes
 */

// Basic cache operations
$router->post('/api/performance-cache/set', 'PerformanceCacheController@set');
$router->get('/api/performance-cache/get', 'PerformanceCacheController@get');
$router->delete('/api/performance-cache/delete', 'PerformanceCacheController@delete');
$router->delete('/api/performance-cache/clear', 'PerformanceCacheController@clear');

// Advanced cache operations
$router->post('/api/performance-cache/remember', 'PerformanceCacheController@remember');
$router->post('/api/performance-cache/memoize', 'PerformanceCacheController@memoize');
$router->post('/api/performance-cache/cache-query', 'PerformanceCacheController@cacheQuery');
$router->post('/api/performance-cache/cache-api-response', 'PerformanceCacheController@cacheApiResponse');
$router->post('/api/performance-cache/cache-computed', 'PerformanceCacheController@cacheComputed');

// Tag-based operations
$router->delete('/api/performance-cache/clear-by-tags', 'PerformanceCacheController@clearByTags');

// Cache management and monitoring
$router->get('/api/performance-cache/stats', 'PerformanceCacheController@getStats');
$router->post('/api/performance-cache/reset-stats', 'PerformanceCacheController@resetStats');
$router->get('/api/performance-cache/info', 'PerformanceCacheController@getCacheInfo');
$router->get('/api/performance-cache/size', 'PerformanceCacheController@getCacheSize');
$router->post('/api/performance-cache/optimize', 'PerformanceCacheController@optimize');
$router->post('/api/performance-cache/warmup', 'PerformanceCacheController@warmUp');
$router->get('/api/performance-cache/report', 'PerformanceCacheController@generateReport');
$router->get('/api/performance-cache/dashboard', 'PerformanceCacheController@getDashboard');

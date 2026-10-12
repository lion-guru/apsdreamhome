<?php

use App\Http\Controllers\RequestMiddlewareController;

/**
 * Request Middleware Routes
 *
 * NOTE: All routes require a valid Bearer API token (ApiAuthMiddleware).
 * Remote middleware registration/application must never be served anonymously.
 */

// Request information routes
$router->get('/api/request-middleware/metadata', 'RequestMiddlewareController@getRequestMetadata')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/request-middleware/client-info', 'RequestMiddlewareController@getClientInfo')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// Security and validation routes
$router->get('/api/request-middleware/detect-suspicious', 'RequestMiddlewareController@detectSuspiciousActivity')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/request-middleware/validate-json', 'RequestMiddlewareController@validateJsonRequest')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/request-middleware/sanitize-input', 'RequestMiddlewareController@sanitizeInput')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// Middleware management routes
$router->get('/api/request-middleware/stats', 'RequestMiddlewareController@getMiddlewareStats')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/request-middleware/available', 'RequestMiddlewareController@getAvailableMiddleware')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/request-middleware/register', 'RequestMiddlewareController@registerMiddleware')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/request-middleware/apply', 'RequestMiddlewareController@applyMiddleware')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// Testing route
$router->get('/api/request-middleware/test', 'RequestMiddlewareController@testMiddleware')->middleware('App\Http\Middleware\ApiAuthMiddleware');

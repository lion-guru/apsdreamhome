<?php

use App\Http\Controllers\RequestMiddlewareController;
use Illuminate\Support\Facades\Route;

/**
 * Request Middleware Routes
 */

// Request information routes
$router->get('/api/request-middleware/metadata', 'RequestMiddlewareController@getRequestMetadata');
$router->get('/api/request-middleware/client-info', 'RequestMiddlewareController@getClientInfo');

// Security and validation routes
$router->get('/api/request-middleware/detect-suspicious', 'RequestMiddlewareController@detectSuspiciousActivity');
$router->post('/api/request-middleware/validate-json', 'RequestMiddlewareController@validateJsonRequest');
$router->post('/api/request-middleware/sanitize-input', 'RequestMiddlewareController@sanitizeInput');

// Middleware management routes
$router->get('/api/request-middleware/stats', 'RequestMiddlewareController@getMiddlewareStats');
$router->get('/api/request-middleware/available', 'RequestMiddlewareController@getAvailableMiddleware');
$router->post('/api/request-middleware/register', 'RequestMiddlewareController@registerMiddleware');
$router->post('/api/request-middleware/apply', 'RequestMiddlewareController@applyMiddleware');

// Testing route
$router->get('/api/request-middleware/test', 'RequestMiddlewareController@testMiddleware');

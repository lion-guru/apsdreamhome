<?php

use App\Http\Controllers\EventControllerNew;
use Illuminate\Support\Facades\Route;

/**
 * Event Bus Management Routes
 */

// Event subscription routes
$router->post('/api/events/subscribe', 'EventControllerNew@subscribe');
$router->post('/api/events/subscribe-wildcard', 'EventControllerNew@subscribeWildcard');
$router->delete('/api/events/unsubscribe', 'EventControllerNew@unsubscribe');
$router->delete('/api/events/unsubscribe-wildcard', 'EventControllerNew@unsubscribeWildcard');

// Event publishing routes
$router->post('/api/events/publish', 'EventControllerNew@publish');

// Event transformation and middleware routes
$router->post('/api/events/add-transformer', 'EventControllerNew@addTransformer');
$router->post('/api/events/add-middleware', 'EventControllerNew@addMiddleware');

// Event management routes
$router->get('/api/events/history', 'EventControllerNew@getHistory');
$router->delete('/api/events/clear-history', 'EventControllerNew@clearHistory');
$router->get('/api/events/subscriptions', 'EventControllerNew@getSubscriptions');

// Event reporting and analytics routes
$router->get('/api/events/report', 'EventControllerNew@generateReport');
$router->get('/api/events/dashboard', 'EventControllerNew@getDashboard');
$router->get('/api/events/statistics', 'EventControllerNew@getStatistics');

// Event demonstration route
$router->post('/api/events/demonstrate', 'EventControllerNew@demonstrate');

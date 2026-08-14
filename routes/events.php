<?php

use App\Http\Controllers\EventController;
use Illuminate\Support\Facades\Route;

/**
 * Event Bus Management Routes
 */

// Event subscription routes
$router->post('/api/events/subscribe', 'EventController@subscribe');
$router->post('/api/events/subscribe-wildcard', 'EventController@subscribeWildcard');
$router->delete('/api/events/unsubscribe', 'EventController@unsubscribe');
$router->delete('/api/events/unsubscribe-wildcard', 'EventController@unsubscribeWildcard');

// Event publishing routes
$router->post('/api/events/publish', 'EventController@publish');

// Event transformation and middleware routes
$router->post('/api/events/add-transformer', 'EventController@addTransformer');
$router->post('/api/events/add-middleware', 'EventController@addMiddleware');

// Event management routes
$router->get('/api/events/history', 'EventController@getHistory');
$router->delete('/api/events/clear-history', 'EventController@clearHistory');
$router->get('/api/events/subscriptions', 'EventController@getSubscriptions');

// Event reporting and analytics routes
$router->get('/api/events/report', 'EventController@generateReport');
$router->get('/api/events/dashboard', 'EventController@getDashboard');
$router->get('/api/events/statistics', 'EventController@getStatistics');

// Event demonstration route
$router->post('/api/events/demonstrate', 'EventController@demonstrate');?>
<?php

use App\Http\Controllers\EventController;

/**
 * Event Bus Management Routes
 *
 * NOTE: All routes require a valid Bearer API token (ApiAuthMiddleware).
 * Publishing/clearing events and managing subscriptions must never be
 * served anonymously.
 */

// Event subscription routes
$router->post('/api/events/subscribe', 'EventController@subscribe')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/events/subscribe-wildcard', 'EventController@subscribeWildcard')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->delete('/api/events/unsubscribe', 'EventController@unsubscribe')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->delete('/api/events/unsubscribe-wildcard', 'EventController@unsubscribeWildcard')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// Event publishing routes
$router->post('/api/events/publish', 'EventController@publish')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// Event transformation and middleware routes
$router->post('/api/events/add-transformer', 'EventController@addTransformer')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/events/add-middleware', 'EventController@addMiddleware')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// Event management routes
$router->get('/api/events/history', 'EventController@getHistory')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->delete('/api/events/clear-history', 'EventController@clearHistory')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/events/subscriptions', 'EventController@getSubscriptions')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// Event reporting and analytics routes
$router->get('/api/events/report', 'EventController@generateReport')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/events/dashboard', 'EventController@getDashboard')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/events/statistics', 'EventController@getStatistics')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// Event demonstration route
$router->post('/api/events/demonstrate', 'EventController@demonstrate')->middleware('App\Http\Middleware\ApiAuthMiddleware');

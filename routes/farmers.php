<?php

/**
 * Farmer Management Routes
 *
 * NOTE: All routes require a valid Bearer API token (ApiAuthMiddleware).
 * These endpoints expose farmer PII (names, land holdings, transactions,
 * loans) — they must never be served anonymously.
 */

// STATIC routes MUST come before parameterized {id} routes
$router->get('/api/farmers/stats', 'FarmerController@stats')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/farmers/summary', 'FarmerController@summary')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/farmers/search', 'FarmerController@search')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// Farmer CRUD routes
$router->get('/api/farmers', 'FarmerController@index')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/farmers', 'FarmerController@store')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/farmers/{id}', 'FarmerController@show')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->put('/api/farmers/{id}', 'FarmerController@update')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// Farmer land holdings routes
$router->get('/api/farmers/{id}/land-holdings', 'FarmerController@landHoldings')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/farmers/{id}/land-holdings', 'FarmerController@addLandHolding')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->put('/api/farmers/land-holdings/{holdingId}/acquisition-status', 'FarmerController@updateAcquisitionStatus')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// Farmer transactions routes
$router->get('/api/farmers/{id}/transactions', 'FarmerController@transactions')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/farmers/{id}/transactions', 'FarmerController@addTransaction')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// Farmer loans routes
$router->get('/api/farmers/{id}/loans', 'FarmerController@loans')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// Farmer support requests routes
$router->get('/api/farmers/{id}/support-requests', 'FarmerController@supportRequests')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/farmers/{id}/support-requests', 'FarmerController@createSupportRequest')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// Farmer dashboard and analytics
$router->get('/api/farmers/{id}/dashboard', 'FarmerController@dashboard')->middleware('App\Http\Middleware\ApiAuthMiddleware');

<?php

/**
 * Farmer Management Routes
 */

// Farmer CRUD routes
$router->get('/api/farmers', 'FarmerController@index');
$router->post('/api/farmers', 'FarmerController@store');
$router->get('/api/farmers/{id}', 'FarmerController@show');
$router->put('/api/farmers/{id}', 'FarmerController@update');

// Farmer land holdings routes
$router->get('/api/farmers/{id}/land-holdings', 'FarmerController@landHoldings');
$router->post('/api/farmers/{id}/land-holdings', 'FarmerController@addLandHolding');
$router->put('/api/farmers/land-holdings/{holdingId}/acquisition-status', 'FarmerController@updateAcquisitionStatus');

// Farmer transactions routes
$router->get('/api/farmers/{id}/transactions', 'FarmerController@transactions');
$router->post('/api/farmers/{id}/transactions', 'FarmerController@addTransaction');

// Farmer loans routes
$router->get('/api/farmers/{id}/loans', 'FarmerController@loans');

// Farmer support requests routes
$router->get('/api/farmers/{id}/support-requests', 'FarmerController@supportRequests');
$router->post('/api/farmers/{id}/support-requests', 'FarmerController@createSupportRequest');

// Farmer dashboard and analytics
$router->get('/api/farmers/{id}/dashboard', 'FarmerController@dashboard');
$router->get('/api/farmers/stats', 'FarmerController@stats');
$router->get('/api/farmers/summary', 'FarmerController@summary');

// Search route
$router->get('/api/farmers/search', 'FarmerController@search');

<?php

use App\Http\Controllers\CoreFunctionsController;
use Illuminate\Support\Facades\Route;

/**
 * Core Functions Routes
 */

// Input validation routes
$router->post('/api/core-functions/validate', 'CoreFunctionsController@validateInput');
$router->post('/api/core-functions/validate-multiple', 'CoreFunctionsController@validateInputs');

// Text processing routes
$router->post('/api/core-functions/format-phone', 'CoreFunctionsController@formatPhone');
$router->post('/api/core-functions/generate-slug', 'CoreFunctionsController@generateSlug');
$router->post('/api/core-functions/truncate-text', 'CoreFunctionsController@truncateText');
$router->post('/api/core-functions/generate-random', 'CoreFunctionsController@generateRandomString');

// Formatting routes
$router->post('/api/core-functions/format-currency', 'CoreFunctionsController@formatCurrency');
$router->post('/api/core-functions/format-date', 'CoreFunctionsController@formatDate');

// File handling routes
$router->post('/api/core-functions/upload-image', 'CoreFunctionsController@uploadImage');
$router->get('/api/core-functions/file-info', 'CoreFunctionsController@getFileInfo');
$router->post('/api/core-functions/extract-text', 'CoreFunctionsController@extractText');

// Client information routes
$router->get('/api/core-functions/client-info', 'CoreFunctionsController@getClientInfo');
$router->get('/api/core-functions/csrf-token', 'CoreFunctionsController@getCsrfToken');

// Logging routes
$router->post('/api/core-functions/log-action', 'CoreFunctionsController@logAction');

// Testing route
$router->get('/api/core-functions/test', 'CoreFunctionsController@test');

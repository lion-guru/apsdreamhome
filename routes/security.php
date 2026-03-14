<?php

use App\Http\Controllers\SecurityController;
use Illuminate\Support\Facades\Route;

/**
 * Security Management Routes
 */

// Security testing routes
$router->get('/api/security/run-tests', 'SecurityController@runTests');
$router->get('/api/security/score', 'SecurityController@getScore');
$router->post('/api/security/generate-report', 'SecurityController@generateReport');
$router->get('/api/security/download-report/{filename}', 'SecurityController@downloadReport');

// Security validation routes
$router->post('/api/security/validate-input', 'SecurityController@validateInput');
$router->post('/api/security/hash-password', 'SecurityController@hashPassword');
$router->post('/api/security/verify-password', 'SecurityController@verifyPassword');
$router->get('/api/security/csrf-token', 'SecurityController@generateCsrfToken');
$router->post('/api/security/validate-csrf', 'SecurityController@validateCsrfToken');

// Security monitoring routes
$router->post('/api/security/check-rate-limit', 'SecurityController@checkRateLimit');
$router->post('/api/security/detect-suspicious', 'SecurityController@detectSuspiciousActivity');
$router->post('/api/security/log-event', 'SecurityController@logSecurityEvent');

// Security dashboard and recommendations
$router->get('/api/security/dashboard', 'SecurityController@getDashboard');
$router->get('/api/security/recommendations', 'SecurityController@getRecommendations');

// Component testing
$router->post('/api/security/test-component', 'SecurityController@testComponent');

<?php

use App\Http\Controllers\ContainerController;
use Illuminate\Support\Facades\Route;

/**
 * Dependency Injection Container Routes
 */

// Container management routes
$router->get('/api/container', 'ContainerController@index');
$router->post('/api/container/register', 'ContainerController@register');
$router->get('/api/container/{id}', 'ContainerController@show');
$router->get('/api/container/{id}/resolve', 'ContainerController@resolve');
$router->delete('/api/container/{id}', 'ContainerController@destroy');
$router->delete('/api/container', 'ContainerController@clear');
$router->get('/api/container/test/functionality', 'ContainerController@test');
$router->get('/api/container/stats/info', 'ContainerController@stats');

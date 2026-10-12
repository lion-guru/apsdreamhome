<?php

/**
 * Dependency Injection Container Routes
 *
 * NOTE: All routes require a valid Bearer API token (ApiAuthMiddleware).
 * Container introspection/registration must never be served anonymously.
 */

// STATIC routes before parameterized {id} routes
$router->get('/api/container/check', 'ContainerController@check')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/container/stats', 'ContainerController@stats')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->post('/api/container/register', 'ContainerController@register')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->delete('/api/container', 'ContainerController@clear')->middleware('App\Http\Middleware\ApiAuthMiddleware');

// Parameterized {id} routes
$router->get('/api/container/{id}', 'ContainerController@show')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->get('/api/container/{id}/resolve', 'ContainerController@resolve')->middleware('App\Http\Middleware\ApiAuthMiddleware');
$router->delete('/api/container/{id}', 'ContainerController@destroy')->middleware('App\Http\Middleware\ApiAuthMiddleware');

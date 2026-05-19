<?php

/**
 * Dependency Injection Container Routes
 */

// STATIC routes before parameterized {id} routes
$router->get('/api/container/test/functionality', 'ContainerController@test');
$router->get('/api/container/stats/info', 'ContainerController@stats');
$router->get('/api/container', 'ContainerController@index');
$router->post('/api/container/register', 'ContainerController@register');
$router->delete('/api/container', 'ContainerController@clear');

// Parameterized {id} routes
$router->get('/api/container/{id}', 'ContainerController@show');
$router->get('/api/container/{id}/resolve', 'ContainerController@resolve');
$router->delete('/api/container/{id}', 'ContainerController@destroy');

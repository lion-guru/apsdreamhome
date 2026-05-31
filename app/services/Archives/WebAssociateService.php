<?php
// Associate Routes - APS Dream Home

// Include router
require_once __DIR__ . '/router.php';

// Initialize router
$router = new Router();

// Associate routes (Modern MVC)
$router->get('/users', 'Associate\AssociateController@index');
$router->get('/users/dashboard', 'Associate\AssociateController@dashboard');
$router->get('/users/create', 'Associate\AssociateController@create');
$router->post('/users/store', 'Associate\AssociateController@store');
$router->get('/users/edit/{id}', 'Associate\AssociateController@edit');
$router->post('/users/update/{id}', 'Associate\AssociateController@update');
$router->get('/users/show/{id}', 'Associate\AssociateController@show');
$router->get('/users/metrics/{id}', 'Associate\AssociateController@metrics');
$router->post('/users/update-status/{id}', 'Associate\AssociateController@updateStatus');
$router->get('/users/delete/{id}', 'Associate\AssociateController@delete');

?>

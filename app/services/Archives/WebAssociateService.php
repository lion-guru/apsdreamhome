<?php
// Associate Routes - APS Dream Home

// Include router
require_once __DIR__ . '/router.php';

// Initialize router
$router = new Router();

// Associate routes (Modern MVC)
$router->get('/associates', 'Associate\AssociateController@index');
$router->get('/associates/dashboard', 'Associate\AssociateController@dashboard');
$router->get('/associates/create', 'Associate\AssociateController@create');
$router->post('/associates/store', 'Associate\AssociateController@store');
$router->get('/associates/edit/{id}', 'Associate\AssociateController@edit');
$router->post('/associates/update/{id}', 'Associate\AssociateController@update');
$router->get('/associates/show/{id}', 'Associate\AssociateController@show');
$router->get('/associates/metrics/{id}', 'Associate\AssociateController@metrics');
$router->post('/associates/update-status/{id}', 'Associate\AssociateController@updateStatus');
$router->get('/associates/delete/{id}', 'Associate\AssociateController@delete');

?>

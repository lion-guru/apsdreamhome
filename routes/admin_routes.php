<?php
// Admin Routes Extension
$router->get('/admin/users', 'App\Http\Controllers\Admin\CustomerController@index');
$router->get('/admin/users/{id}', 'App\Http\Controllers\Admin\CustomerController@show');
$router->get('/admin/users/{id}/edit', 'App\Http\Controllers\Admin\CustomerController@edit');
$router->get('/admin/sales', 'App\Http\Controllers\Admin\SalesController@index');

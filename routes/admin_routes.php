<?php
// Admin Routes Extension
$router->get('/admin/customers', 'App\Http\Controllers\Admin\CustomerController@index');
$router->get('/admin/customers/{id}', 'App\Http\Controllers\Admin\CustomerController@show');
$router->get('/admin/customers/{id}/edit', 'App\Http\Controllers\Admin\CustomerController@edit');
$router->get('/admin/sales', 'App\Http\Controllers\Admin\SalesController@index');

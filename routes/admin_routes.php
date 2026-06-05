<?php
// Admin Routes Extension
// Note: /admin/users and /admin/sales are already defined in web.php with proper controllers.
// Routes here are kept for legacy compatibility only and must reference existing controllers.
$router->get('/admin/sales', 'App\Http\Controllers\Admin\SalesController@index');

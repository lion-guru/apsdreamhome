<?php
$file = 'C:/xampp/htdocs/apsdreamhome/routes/web.php';
$content = file_get_contents($file);

$old = <<<'EOF'
$router->get('/admin/document-esign', 'App\Http\Controllers\Admin\DocumentEsignController@index');
$router->post('/admin/document-esign/store', 'App\Http\Controllers\Admin\DocumentEsignController@store');
$router->get('/admin/document-esign/{id}', 'App\Http\Controllers\Admin\DocumentEsignController@show');
$router->post('/admin/document-esign/{id}/sign', 'App\Http\Controllers\Admin\DocumentEsignController@sign');
$router->post('/admin/document-esign/{id}/cancel', 'App\Http\Controllers\Admin\DocumentEsignController@cancel');
$router->get('/api/v2/audit/log', 'Admin\AuditLogController@api');
$router->get('/api/v2/notifications/poll', 'Api\NotificationStreamController@poll');
$router->post('/api/v2/notifications/read', 'Api\NotificationStreamController@markRead');
$router->get('/api/v2/notifications/stream', 'Api\NotificationStreamController@stream');
EOF;

$new = <<<'EOF'
// Property Verification Badge management
$router->get('/admin/property-verification', 'App\Http\Controllers\Admin\PropertyVerificationController@index');
$router->get('/admin/property-verification/levels', 'App\Http\Controllers\Admin\PropertyVerificationController@levels');
$router->post('/admin/property-verification/levels/store', 'App\Http\Controllers\Admin\PropertyVerificationController@storeLevel');
$router->post('/admin/property-verification/levels/{id}/update', 'App\Http\Controllers\Admin\PropertyVerificationController@updateLevel');
$router->post('/admin/property-verification/levels/{id}/delete', 'App\Http\Controllers\Admin\PropertyVerificationController@deleteLevel');
$router->get('/admin/property-verification/requests', 'App\Http\Controllers\Admin\PropertyVerificationController@requests');
$router->get('/admin/property-verification/requests/{id}', 'App\Http\Controllers\Admin\PropertyVerificationController@showRequest');
$router->post('/admin/property-verification/requests/{id}/approve', 'App\Http\Controllers\Admin\PropertyVerificationController@approveRequest');
$router->post('/admin/property-verification/requests/{id}/reject', 'App\Http\Controllers\Admin\PropertyVerificationController@rejectRequest');
$router->post('/admin/property-verification/requests/{id}/assign', 'App\Http\Controllers\Admin\PropertyVerificationController@assignRequest');
$router->get('/admin/property-verification/badges', 'App\Http\Controllers\Admin\PropertyVerificationController@badges');
$router->post('/admin/property-verification/badges/{id}/revoke', 'App\Http\Controllers\Admin\PropertyVerificationController@revokeBadge');
$router->get('/admin/property-verification/stats', 'App\Http\Controllers\Admin\PropertyVerificationController@stats');

// Document E-Sign management
$router->get('/admin/document-esign', 'App\Http\Controllers\Admin\DocumentEsignController@index');
$router->post('/admin/document-esign/store', 'App\Http\Controllers\Admin\DocumentEsignController@store');
$router->get('/admin/document-esign/{id}', 'App\Http\Controllers\Admin\DocumentEsignController@show');
$router->post('/admin/document-esign/{id}/sign', 'App\Http\Controllers\Admin\DocumentEsignController@sign');
$router->post('/admin/document-esign/{id}/cancel', 'App\Http\Controllers\Admin\DocumentEsignController@cancel');
$router->get('/api/v2/audit/log', 'Admin\AuditLogController@api');
$router->get('/api/v2/notifications/poll', 'Api\NotificationStreamController@poll');
$router->post('/api/v2/notifications/read', 'Api\NotificationStreamController@markRead');
$router->get('/api/v2/notifications/stream', 'Api\NotificationStreamController@stream');
EOF;

$content = str_replace($old, $new, $content);
file_put_contents($file, $content);
echo "Routes updated successfully\n";
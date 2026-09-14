#!/usr/bin/env python3
import re

file_path = 'C:/xampp/htdocs/apsdreamhome/routes/web.php'

with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# Find the section to replace
pattern = re.compile(
    r"\$router->get\('/admin/document-esign'.*?\$router->get\('/api/v2/notifications/stream'.*?Api\\\\NotificationStreamController@stream\);",
    re.DOTALL
)

match = pattern.search(content)
if match:
    old_text = match.group(0)
    print(f"Found match at position {match.start()}, length {len(old_text)}")
    print("First 200 chars of match:", old_text[:200])
    
    new_text = """// Property Verification Badge management
$router->get('/admin/property-verification', 'App\\Http\\Controllers\\Admin\\PropertyVerificationController@index');
$router->get('/admin/property-verification/levels', 'App\\Http\\Controllers\\Admin\\PropertyVerificationController@levels');
$router->post('/admin/property-verification/levels/store', 'App\\Http\\Controllers\\Admin\\PropertyVerificationController@storeLevel');
$router->post('/admin/property-verification/levels/{id}/update', 'App\\Http\\Controllers\\Admin\\PropertyVerificationController@updateLevel');
$router->post('/admin/property-verification/levels/{id}/delete', 'App\\Http\\Controllers\\Admin\\PropertyVerificationController@deleteLevel');
$router->get('/admin/property-verification/requests', 'App\\Http\\Controllers\\Admin\\PropertyVerificationController@requests');
$router->get('/admin/property-verification/requests/{id}', 'App\\Http\\Controllers\\Admin\\PropertyVerificationController@showRequest');
$router->post('/admin/property-verification/requests/{id}/approve', 'App\\Http\\Controllers\\Admin\\PropertyVerificationController@approveRequest');
$router->post('/admin/property-verification/requests/{id}/reject', 'App\\Http\\Controllers\\Admin\\PropertyVerificationController@rejectRequest');
$router->post('/admin/property-verification/requests/{id}/assign', 'App\\Http\\Controllers\\Admin\\PropertyVerificationController@assignRequest');
$router->get('/admin/property-verification/badges', 'App\\Http\\Controllers\\Admin\\PropertyVerificationController@badges');
$router->post('/admin/property-verification/badges/{id}/revoke', 'App\\Http\\Controllers\\Admin\\PropertyVerificationController@revokeBadge');
$router->get('/admin/property-verification/stats', 'App\\Http\\Controllers\\Admin\\PropertyVerificationController@stats');

// Document E-Sign management
$router->get('/admin/document-esign', 'App\\Http\\Controllers\\Admin\\DocumentEsignController@index');
$router->post('/admin/document-esign/store', 'App\\Http\\Controllers\\Admin\\DocumentEsignController@store');
$router->get('/admin/document-esign/{id}', 'App\\Http\\Controllers\\Admin\\DocumentEsignController@show');
$router->post('/admin/document-esign/{id}/sign', 'App\\Http\\Controllers\\Admin\\DocumentEsignController@sign');
$router->post('/admin/document-esign/{id}/cancel', 'App\\Http\\Controllers\\Admin\\DocumentEsignController@cancel');
$router->get('/api/v2/audit/log', 'Admin\\AuditLogController@api');
$router->get('/api/v2/notifications/poll', 'Api\\NotificationStreamController@poll');
$router->post('/api/v2/notifications/read', 'Api\\NotificationStreamController@markRead');
$router->get('/api/v2/notifications/stream', 'Api\\NotificationStreamController@stream');"""

    content = content[:match.start()] + new_text + content[match.end():]
    with open(file_path, 'w', encoding='utf-8') as f:
        f.write(content)
    print("Routes updated successfully!")
else:
    print("Pattern not found!")
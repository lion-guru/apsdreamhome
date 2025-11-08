# API Authentication System - Complete Guide

## Overview
This document describes the unified API authentication system that integrates the existing `ApiKeyManager` with the new API authentication endpoints. The system provides secure API key management with permission-based access control, rate limiting, and comprehensive logging.

## Key Components

### 1. ApiKeyManager Class (`includes/ApiKeyManager.php`)
The core class responsible for API key management:
- **Key Generation**: Creates SHA-256 hashed API keys
- **Key Validation**: Validates keys with permission checking
- **Rate Limiting**: Enforces request limits
- **Usage Tracking**: Monitors API usage statistics
- **Permission Management**: Handles wildcard and specific permissions

### 2. API Authentication Endpoints (`api/auth/api_keys.php`)
HTTP endpoints for API key management:
- **Key Generation**: `POST /api/auth/generate`
- **Key Validation**: `POST /api/auth/validate` 
- **Key Revocation**: `POST /api/auth/revoke`
- **Key Listing**: `GET /api/auth/keys`

### 3. Authentication Middleware (`includes/middleware/api_auth_middleware.php`)
Middleware for protecting API routes with authentication and permission requirements.

## Database Schema

### api_keys Table
```sql
CREATE TABLE api_keys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    api_key_hash VARCHAR(64) NOT NULL UNIQUE, -- SHA-256 hash
    permissions TEXT NOT NULL, -- JSON encoded permissions array
    rate_limit INT DEFAULT 100,
    requests_made INT DEFAULT 0,
    last_used TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

## Permission System

### Permission Types
- **Wildcard Permission**: `"*"` - Full access to all endpoints
- **Specific Permissions**: Array of specific permissions like `["leads.read", "leads.write", "users.read"]`

### Available Permissions
- `leads.read` - Read access to leads data
- `leads.write` - Write access to leads data  
- `users.read` - Read access to user data
- `users.write` - Write access to user data
- `reports.read` - Access to reporting data
- `system.admin` - Administrative system access

## Usage Examples

### 1. Generating API Keys

#### PHP Code Example
```php
<?php
require_once 'includes/ApiKeyManager.php';

$apiKeyManager = new ApiKeyManager();

// Generate key with wildcard permissions
$apiKey = $apiKeyManager->generateKey(1, ['*']);

// Generate key with specific permissions
$apiKey = $apiKeyManager->generateKey(1, ['leads.read', 'leads.write']);

// Generate key with expiration (30 days)
$apiKey = $apiKeyManager->generateKey(1, ['leads.read'], 30);
```

#### HTTP API Example
```bash
# Generate key with wildcard permissions
curl -X POST http://localhost/api/auth/generate \
  -H "Content-Type: application/json" \
  -d '{"user_id": 1, "permissions": ["*"]}'

# Generate key with specific permissions  
curl -X POST http://localhost/api/auth/generate \
  -H "Content-Type: application/json" \
  -d '{"user_id": 1, "permissions": ["leads.read", "leads.write"]}'
```

### 2. Validating API Keys

#### PHP Code Example
```php
<?php
require_once 'includes/ApiKeyManager.php';

$apiKeyManager = new ApiKeyManager();

// Validate key without permission checking
$isValid = $apiKeyManager->validateKey($apiKey);

// Validate key with permission requirements
$isValid = $apiKeyManager->validateKey($apiKey, ['leads.read']);

// Get validation details
$validationResult = $apiKeyManager->validateKey($apiKey, ['leads.read']);
if ($validationResult['valid']) {
    echo "User ID: " . $validationResult['user_id'];
    echo "Permissions: " . implode(', ', $validationResult['permissions']);
}
```

#### HTTP API Example
```bash
# Validate API key
curl -X POST http://localhost/api/auth/validate \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -d '{"required_permissions": ["leads.read"]}'
```

### 3. Using Authentication Middleware

#### Protecting API Routes
```php
<?php
// Protect route with authentication and specific permissions
$app->get('/api/leads', function ($request, $response) {
    // Middleware ensures valid API key with leads.read permission
    $leads = getLeads();
    return $response->withJson($leads);
})->add(new ApiAuthMiddleware(['leads.read']));

// Protect route with wildcard permissions
$app->get('/api/admin/stats', function ($request, $response) {
    // Middleware ensures valid API key with admin access
    $stats = getAdminStats();
    return $response->withJson($stats);
})->add(new ApiAuthMiddleware(['system.admin']));

// Public route (no authentication required)
$app->get('/api/public/info', function ($request, $response) {
    $info = getPublicInfo();
    return $response->withJson($info);
})->add(new ApiAuthMiddleware([], true)); // public=true
```

## Integration with Existing Systems

### CRM System Integration
```php
// In sel_force_crm_system.php
$apiKeyManager = new ApiKeyManager();

// Validate API key for CRM access
if ($apiKeyManager->validateKey($apiKey, ['leads.read', 'leads.write'])) {
    // Process CRM operations
    processLeads();
}
```

### Colonizer System Integration
```php
// In colonizer_system.php  
$apiKeyManager = new ApiKeyManager();

// Validate API key with system admin permissions
if ($apiKeyManager->validateKey($apiKey, ['system.admin'])) {
    // Perform system administration tasks
    performSystemAdminTasks();
}
```

### Admin Panel Integration
```php
// In admin/api_keys.php
$apiKeyManager = new ApiKeyManager();

// Generate keys through admin interface
if ($_POST['action'] == 'generate_key') {
    $permissions = json_decode($_POST['permissions'], true);
    $apiKey = $apiKeyManager->generateKey($_POST['user_id'], $permissions);
    // Display key to admin
}

// List all API keys
$allKeys = $apiKeyManager->getAllKeys();
```

## Error Handling

### Common Error Responses

#### HTTP 401 Unauthorized
```json
{
    "success": false,
    "error": "Invalid API key",
    "code": "INVALID_API_KEY"
}
```

#### HTTP 403 Forbidden
```json
{
    "success": false, 
    "error": "Insufficient permissions",
    "code": "INSUFFICIENT_PERMISSIONS",
    "required": ["leads.read"],
    "has": ["users.read"]
}
```

#### HTTP 429 Too Many Requests
```json
{
    "success": false,
    "error": "Rate limit exceeded",
    "code": "RATE_LIMIT_EXCEEDED",
    "limit": 100,
    "remaining": 0,
    "reset_in": 3600
}
```

## Security Best Practices

1. **Always use HTTPS** for API communication
2. **Store API keys securely** - never in version control
3. **Rotate keys regularly** - implement key expiration
4. **Monitor usage** - track abnormal API activity
5. **Implement rate limiting** - prevent abuse
6. **Validate permissions** - principle of least privilege
7. **Log authentication events** - for security auditing

## Migration Guide

### From Old System to New Unified System

1. **Database Migration**: The `api_keys` table structure remains compatible
2. **Code Updates**: Update `validateKey()` calls to include permission parameters if needed
3. **Backward Compatibility**: Existing code continues to work without changes
4. **New Features**: Gradually adopt permission-based authentication

## Troubleshooting

### Common Issues

1. **Permission denied errors**: Check that API key has required permissions
2. **Rate limit errors**: Increase rate limit or implement exponential backoff
3. **Key validation failures**: Verify API key format and database connection
4. **CORS issues**: Configure proper CORS headers in API responses

### Debug Mode
Enable debug mode in `ApiKeyManager` for detailed error information:

```php
$apiKeyManager = new ApiKeyManager();
$apiKeyManager->setDebug(true); // Enable detailed error messages
```

## Support
For technical support regarding the API authentication system, contact the development team or refer to the system logs in `logs/api_auth.log`.

---
*Last Updated: [Current Date]*
*Version: 2.0.0*
# API Authentication

## Overview

This document outlines the authentication mechanisms available for the APS Dream Home API, including API key authentication and OAuth 2.0.

## Authentication Methods

### 1. API Key Authentication

#### Obtaining an API Key
1. Log in to your account at [https://developer.apsdreamhome.com](https://developer.apsdreamhome.com)
2. Navigate to "API Keys"
3. Click "Create New API Key"
4. Copy the generated API key (only shown once)

#### Using the API Key
Include the API key in the `Authorization` header of your requests:

```http
GET /api/v1/properties HTTP/1.1
Host: api.apsdreamhome.com
Authorization: Bearer your-api-key-here
Accept: application/json
```

### 2. OAuth 2.0 Authentication

#### Registering an Application
1. Log in to the developer portal
2. Navigate to "My Applications"
3. Click "Create New Application"
4. Fill in the application details
5. Note your `client_id` and `client_secret`

#### Authorization Code Flow
1. Redirect users to authorize your application:
   ```
   GET https://api.apsdreamhome.com/oauth/authorize?client_id=your-client-id&redirect_uri=your-redirect-uri&response_type=code&scope=read-properties
   ```

2. Exchange the authorization code for an access token:
   ```http
   POST /oauth/token HTTP/1.1
   Host: api.apsdreamhome.com
   Content-Type: application/x-www-form-urlencoded
   
   grant_type=authorization_code&
   client_id=your-client-id&
   client_secret=your-client-secret&
   redirect_uri=your-redirect-uri&
   code=authorization-code
   ```

3. Use the access token to make API requests:
   ```http
   GET /api/v1/properties HTTP/1.1
   Host: api.apsdreamhome.com
   Authorization: Bearer your-access-token
   Accept: application/json
   ```

#### Client Credentials Flow (Machine-to-Machine)

```http
POST /oauth/token HTTP/1.1
Host: api.apsdreamhome.com
Content-Type: application/x-www-form-urlencoded

grant_type=client_credentials&
client_id=your-client-id&
client_secret=your-client-secret&
scope=read-properties
```

## Scopes

| Scope | Description |
|-------|-------------|
| read-properties | Read property listings |
| write-properties | Create/update properties |
| read-leads | View leads |
| write-leads | Create/update leads |
| read-users | View user information |
| write-users | Manage users |
| * | All scopes |

## Rate Limiting

- **Unauthenticated**: 60 requests per minute
- **API Key**: 1,000 requests per minute
- **OAuth**: 10,000 requests per minute

## Error Responses

### Invalid Authentication
```json
{
  "error": "invalid_authentication",
  "message": "The provided API key or token is invalid"
}
```

### Insufficient Permissions
```json
{
  "error": "insufficient_permissions",
  "message": "The provided credentials don't have permission to access this resource"
}
```

### Rate Limit Exceeded
```json
{
  "error": "rate_limit_exceeded",
  "message": "API rate limit exceeded"
}
```

## Best Practices

1. **Never expose your API keys** in client-side code or public repositories
2. **Rotate API keys** regularly
3. **Use the principle of least privilege** when requesting scopes
4. **Implement exponential backoff** for handling rate limits
5. **Cache access tokens** to reduce authentication requests

## Revoking Access

### Revoke an API Key
1. Log in to the developer portal
2. Navigate to "API Keys"
3. Click "Revoke" next to the key you want to revoke

### Revoke OAuth Tokens
```http
POST /oauth/revoke HTTP/1.1
Host: api.apsdreamhome.com
Content-Type: application/x-www-form-urlencoded

token=token-to-revoke&
client_id=your-client-id&
client_secret=your-client-secret
```

## SDKs

### Official SDKs

#### PHP
```bash
composer require aps-dreamhome/sdk
```

#### JavaScript/Node.js
```bash
npm install @aps-dreamhome/sdk
```

### Example Usage (PHP)

```php
use ApsDreamHome\ApiClient;

$client = new ApiClient([
    'api_key' => 'your-api-key',
    // or 'access_token' => 'your-oauth-token'
]);

// Get properties
$properties = $client->properties->list([
    'status' => 'available',
    'min_price' => 100000,
    'max_price' => 500000,
    'bedrooms' => 3,
]);

// Create a lead
$lead = $client->leads->create([
    'first_name' => 'John',
    'last_name' => 'Doe',
    'email' => 'john.doe@example.com',
    'phone' => '+1234567890',
    'message' => 'Interested in property #123',
    'property_id' => 123
]);
```

### Example Usage (JavaScript)

```javascript
const { ApiClient } = require('@aps-dreamhome/sdk');

const client = new ApiClient({
  apiKey: 'your-api-key',
  // or accessToken: 'your-oauth-token'
});

// Get properties
const properties = await client.properties.list({
  status: 'available',
  minPrice: 100000,
  maxPrice: 500000,
  bedrooms: 3
});

// Create a lead
const lead = await client.leads.create({
  firstName: 'John',
  lastName: 'Doe',
  email: 'john.doe@example.com',
  phone: '+1234567890',
  message: 'Interested in property #123',
  propertyId: 123
});
```

## Webhooks

### Setting Up Webhooks
1. Log in to the developer portal
2. Navigate to "Webhooks"
3. Click "Create Webhook"
4. Enter the callback URL and select events to subscribe to
5. Verify the webhook using the challenge token

### Verifying Webhook Signatures
Webhook requests include a signature in the `X-APS-Signature` header. Verify it using your webhook secret:

```php
$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_APS_SIGNATURE'];
$secret = 'your-webhook-secret';

$computedSignature = hash_hmac('sha256', $payload, $secret);

if (!hash_equals($signature, $computedSignature)) {
    // Invalid signature
    http_response_code(401);
    exit('Invalid signature');
}
```

## IP Whitelisting

For additional security, you can restrict API access to specific IP addresses:
1. Log in to the developer portal
2. Navigate to "API Access"
3. Add IP addresses to the whitelist

## Support

For authentication and authorization support:
- Email: api-support@apsdreamhome.com
- Documentation: [https://developer.apsdreamhome.com/docs](https://developer.apsdreamhome.com/docs)
- Status: [https://status.apsdreamhome.com](https://status.apsdreamhome.com)

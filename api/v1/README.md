# APS Dream Home API Documentation

Welcome to the APS Dream Home API documentation. This API allows you to interact with the APS Dream Home platform programmatically.

## Base URL

All API endpoints are relative to the base URL:
```
https://yourdomain.com/api/v1/
```

## Authentication

Most API endpoints require authentication. Include the API key in the `Authorization` header:

```
Authorization: Bearer YOUR_API_KEY
```

## Rate Limiting

- **Rate Limit**: 100 requests per minute per API key (configurable per user)
- **Headers**:
  - `X-RateLimit-Limit`: Maximum requests allowed
  - `X-RateLimit-Remaining`: Remaining requests in current window
  - `X-RateLimit-Reset`: Timestamp when the limit resets

## Response Format

All responses are in JSON format with the following structure:

```json
{
  "success": true,
  "message": "Descriptive message",
  "data": {}
}
```

## Error Handling

Errors follow this format:

```json
{
  "success": false,
  "error": "Error message",
  "code": 400
}
```

## Client Libraries

We provide official client libraries for multiple programming languages to make integration easier.

### PHP Client

```php
require_once 'api/client/ApsDreamClient.php';

$client = new ApsDreamClient('http://localhost/apsdreamhome/api/v1');

try {
    // Login
    $login = $client->login('admin@example.com', 'password');
    echo "Logged in as: " . $login['user']['email'] . "\n";
    
    // Get available properties
    $properties = $client->getProperties(['status' => 'available']);
    print_r($properties);
    
    // Get current profile
    $profile = $client->getProfile();
    print_r($profile);
    
    // Logout
    $client->logout();
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . " (Code: " . $e->getCode() . ")\n";
}
```

### JavaScript/Node.js Client

```javascript
// In browser:
// <script src="/api/client/ApsDreamClient.js"></script>
// const client = new ApsDreamClient('http://localhost/apsdreamhome/api/v1');

// In Node.js:
// const ApsDreamClient = require('./api/client/ApsDreamClient');

const client = new ApsDreamClient('http://localhost/apsdreamhome/api/v1', null, { debug: true });

async function fetchData() {
  try {
    // Login
    const login = await client.login('admin@example.com', 'password');
    console.log('Logged in as:', login.user.email);
    
    // Get available properties
    const properties = await client.getProperties({ status: 'available' });
    console.log('Available properties:', properties);
    
    // Get current profile
    const profile = await client.getProfile();
    console.log('Profile:', profile);
    
    // Logout
    await client.logout();
    
  } catch (error) {
    console.error('Error:', error.message);
  }
}

fetchData();
```

### Python Client

```python
from aps_dream_client import ApsDreamClient

# Initialize the client
client = ApsDreamClient(
    base_url='http://localhost/apsdreamhome/api/v1',
    debug=True
)

try:
    # Login
    login_response = client.login('admin@example.com', 'password')
    print("Logged in as:", login_response.get('user', {}).get('email'))
    
    # Get available properties
    properties = client.get_properties(status='available')
    print(f"Found {len(properties)} available properties")
    
    # Get current profile
    profile = client.get_profile()
    print("Profile:", profile)
    
    # Logout
    logout_response = client.logout()
    print("Logged out")
    
except Exception as e:
    print(f"Error: {str(e)}")
```

## API Endpoints

### Authentication

#### Login
```
POST /auth/login
```

**Request:**
```json
{
  "email": "user@example.com",
  "password": "yourpassword"
}
```

**Response:**
```json
{
  "success": true,
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "user": {
    "id": 1,
    "email": "user@example.com",
    "first_name": "John",
    "last_name": "Doe",
    "role": "admin"
  }
}
```

#### Logout
```
POST /auth/logout
```

### Profile

#### Get Current User
```
GET /profile
```

#### Update Profile
```
PUT /profile
```

### Properties

#### List Properties
```
GET /properties
```

**Query Parameters:**
- `status` (string, optional): Filter by status (e.g., 'available', 'sold')
- `min_price` (number, optional): Minimum price
- `max_price` (number, optional): Maximum price
- `bedrooms` (number, optional): Number of bedrooms
- `bathrooms` (number, optional): Number of bathrooms
- `limit` (number, optional): Results per page (default: 10)
- `offset` (number, optional): Offset for pagination (default: 0)

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Luxury Villa",
      "price": 500000,
      "status": "available",
      "bedrooms": 4,
      "bathrooms": 3,
      "area": 2500,
      "address": "123 Beach Road"
    }
  ],
  "pagination": {
    "total": 1,
    "limit": 10,
    "offset": 0
  }
}
```

#### Get Property
```
GET /properties/{id}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Luxury Villa",
    "description": "Beautiful luxury villa with ocean view",
    "price": 500000,
    "bedrooms": 4,
    "bathrooms": 3,
    "area": 2500,
    "status": "available",
    "features": ["Pool", "Garden", "Garage"],
    "images": ["image1.jpg", "image2.jpg"],
    "address": "123 Beach Road, Malibu, CA",
    "created_at": "2023-01-01T00:00:00Z",
    "updated_at": "2023-01-01T00:00:00Z"
  }
}
```

#### Create Property
```
POST /properties
```

**Request:**
```json
{
  "title": "New Property",
  "description": "Beautiful new property",
  "price": 350000,
  "bedrooms": 3,
  "bathrooms": 2,
  "area": 1800,
  "status": "available",
  "features": ["Garden", "Garage"],
  "address": "456 Park Ave, New York, NY"
}
```

#### Update Property
```
PUT /properties/{id}
```

#### Delete Property
```
DELETE /properties/{id}
```

### Users (Admin Only)

#### List Users
```
GET /users
```

#### Get User
```
GET /users/{id}
```

#### Create User
```
POST /users
```

#### Update User
```
PUT /users/{id}
```

#### Delete User
```
DELETE /users/{id}
```

## Error Codes

| Code | Status | Description |
|------|--------|-------------|
| 400 | Bad Request | Invalid request parameters |
| 401 | Unauthorized | Missing or invalid authentication |
| 403 | Forbidden | Insufficient permissions |
| 404 | Not Found | Resource not found |
| 422 | Unprocessable Entity | Validation error |
| 429 | Too Many Requests | Rate limit exceeded |
| 500 | Internal Server Error | Server error |

## Support

For support, please contact [support@apsdreamhome.com](mailto:support@apsdreamhome.com).

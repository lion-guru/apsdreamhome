# APS Dream Home - API Documentation

## Overview

The APS Dream Home API provides comprehensive RESTful endpoints for mobile applications and third-party integrations. Built with security, performance, and scalability in mind.

## Base URL

```
https://api.apsdreamhome.com/v1/
```

## Authentication

Currently, the API does not require authentication for basic operations. User-specific features require `user_id` parameter.

## Response Format

All API responses follow this structure:

```json
{
    "success": true/false,
    "data": {...} | "error": "error message",
    "message": "optional message",
    "meta": {
        "timestamp": "2024-01-01T12:00:00Z",
        "version": "1.0.0"
    }
}
```

## Endpoints

### 1. Property Types

**Endpoint:** `GET /api/property-types`

**Description:** Get all available property types for filtering

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Apartment",
            "description": "Modern apartments with amenities"
        },
        {
            "id": 2,
            "name": "Villa",
            "description": "Luxury independent houses"
        }
    ]
}
```

### 2. Cities

**Endpoint:** `GET /api/cities`

**Description:** Get all available cities for location filtering

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Gorakhpur",
            "state": "Uttar Pradesh",
            "coordinates": {
                "lat": 26.7606,
                "lng": 83.3732
            }
        }
    ]
}
```

### 3. Properties

**Endpoint:** `GET /api/properties`

**Description:** Get properties with pagination and filtering

**Parameters:**
- `page` (optional): Page number (default: 1)
- `limit` (optional): Items per page (default: 10, max: 50)
- `property_type` (optional): Filter by property type
- `city` (optional): Filter by city
- `min_price` (optional): Minimum price filter
- `max_price` (optional): Maximum price filter
- `featured` (optional): Show only featured properties
- `sort` (optional): Sort by (price, date, featured)
- `order` (optional): Sort order (asc, desc)

**Response:**
```json
{
    "success": true,
    "data": {
        "properties": [
            {
                "id": 1,
                "title": "Luxury Villa",
                "description": "Beautiful villa with modern amenities",
                "price": 5000000,
                "city": "Gorakhpur",
                "state": "Uttar Pradesh",
                "property_type": "Villa",
                "bedrooms": 3,
                "bathrooms": 2,
                "area_sqft": 2000,
                "images": [
                    {
                        "id": 1,
                        "url": "uploads/properties/1/image1.jpg",
                        "alt": "Living room"
                    }
                ],
                "features": [
                    {
                        "id": 1,
                        "name": "Swimming Pool",
                        "icon": "fas fa-swimming-pool"
                    }
                ],
                "latitude": 26.7606,
                "longitude": 83.3732,
                "featured": true,
                "status": "available"
            }
        ],
        "pagination": {
            "current_page": 1,
            "per_page": 10,
            "total_pages": 15,
            "total_count": 145
        },
        "filters": {
            "property_type": "Villa",
            "city": "Gorakhpur"
        }
    }
}
```

### 4. Single Property

**Endpoint:** `GET /api/property/{id}`

**Description:** Get detailed information about a specific property

**Parameters:**
- `id` (required): Property ID

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "title": "Luxury Villa",
        "description": "Beautiful villa with modern amenities",
        "price": 5000000,
        "city": "Gorakhpur",
        "state": "Uttar Pradesh",
        "property_type": "Villa",
        "bedrooms": 3,
        "bathrooms": 2,
        "area_sqft": 2000,
        "latitude": 26.7606,
        "longitude": 83.3732,
        "status": "available",
        "featured": true,
        "images": [
            {
                "id": 1,
                "url": "uploads/properties/1/image1.jpg",
                "alt": "Living room",
                "is_main": true
            }
        ],
        "features": [
            {
                "id": 1,
                "name": "Swimming Pool",
                "icon": "fas fa-swimming-pool"
            }
        ],
        "created_at": "2024-01-01T10:00:00Z"
    }
}
```

### 5. Submit Inquiry

**Endpoint:** `POST /api/inquiry`

**Description:** Submit a property inquiry

**Headers:**
```
Content-Type: application/json
```

**Request Body:**
```json
{
    "property_id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+91-9876543210",
    "message": "I'm interested in this property. Please contact me.",
    "inquiry_type": "general"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Inquiry submitted successfully",
    "data": {
        "inquiry_id": 123,
        "property_title": "Luxury Villa",
        "submitted_at": "2024-01-01T12:00:00Z"
    }
}
```

### 6. Favorites Management

#### Toggle Favorite

**Endpoint:** `POST /api/favorites/toggle`

**Description:** Add or remove property from favorites

**Request Body:**
```json
{
    "property_id": 1,
    "user_id": 123
}
```

**Response:**
```json
{
    "success": true,
    "message": "Added to favorites",
    "data": {
        "is_favorited": true,
        "property_id": 1,
        "user_id": 123
    }
}
```

#### Get User Favorites

**Endpoint:** `GET /api/favorites/{user_id}`

**Description:** Get all favorited properties for a user

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "title": "Luxury Villa",
            "price": 5000000,
            "city": "Gorakhpur",
            "image": "uploads/properties/1/image1.jpg",
            "favorited_at": "2024-01-01T10:00:00Z"
        }
    ]
}
```

## Error Handling

### HTTP Status Codes

- `200` - Success
- `400` - Bad Request (missing/invalid parameters)
- `404` - Not Found (property/user not found)
- `405` - Method Not Allowed
- `500` - Internal Server Error

### Error Response Format

```json
{
    "success": false,
    "error": "Property not found",
    "message": "The requested property could not be found",
    "error_code": 404
}
```

## Rate Limiting

- **Requests per minute:** 100 requests
- **Burst limit:** 20 requests
- Rate limit headers included in responses

## CORS Support

All API endpoints support CORS:

```
Access-Control-Allow-Origin: *
Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE
Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With
```

## Data Formats

### Property Types
- Apartment
- Villa
- Plot
- Commercial
- Office Space
- Warehouse

### Inquiry Types
- General inquiry
- Viewing request
- Price inquiry
- Documentation request

### Property Status
- Available
- Sold
- Rented
- Under Construction
- Coming Soon

## Integration Examples

### JavaScript (Fetch API)

```javascript
// Get properties with filters
const getProperties = async (filters = {}) => {
    const params = new URLSearchParams(filters);
    const response = await fetch(`/api/properties?${params}`);
    return response.json();
};

// Submit inquiry
const submitInquiry = async (inquiryData) => {
    const response = await fetch('/api/inquiry', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(inquiryData)
    });
    return response.json();
};

// Toggle favorite
const toggleFavorite = async (propertyId, userId) => {
    const response = await fetch('/api/favorites/toggle', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ property_id: propertyId, user_id: userId })
    });
    return response.json();
};
```

### Python (Requests)

```python
import requests

# Get properties
def get_properties(city=None, property_type=None):
    params = {}
    if city:
        params['city'] = city
    if property_type:
        params['property_type'] = property_type

    response = requests.get('https://api.apsdreamhome.com/v1/properties', params=params)
    return response.json()

# Submit inquiry
def submit_inquiry(property_id, name, email, phone, message):
    data = {
        'property_id': property_id,
        'name': name,
        'email': email,
        'phone': phone,
        'message': message
    }

    response = requests.post('https://api.apsdreamhome.com/v1/inquiry', json=data)
    return response.json()
```

### Mobile App (React Native)

```javascript
// Property search
const searchProperties = async (searchCriteria) => {
    try {
        const response = await fetch(`${API_BASE_URL}/properties`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(searchCriteria)
        });
        return await response.json();
    } catch (error) {
        console.error('Search error:', error);
        throw error;
    }
};

// Get property details
const getPropertyDetails = async (propertyId) => {
    try {
        const response = await fetch(`${API_BASE_URL}/property/${propertyId}`);
        return await response.json();
    } catch (error) {
        console.error('Property details error:', error);
        throw error;
    }
};
```

## Security Features

### Input Validation
- All inputs are sanitized and validated
- SQL injection protection via PDO
- XSS protection via HTML escaping

### Rate Limiting
- API rate limiting implemented
- Brute force protection
- DDoS mitigation

### Data Encryption
- Sensitive data encrypted at rest
- Secure session management
- HTTPS required for all requests

## Performance

### Caching
- Database query caching
- Static asset caching
- API response caching

### Optimization
- Database indexes on frequently queried columns
- Optimized image loading
- Lazy loading for better performance

### Monitoring
- API response time monitoring
- Error tracking and alerting
- Performance metrics collection

## Versioning

API versioning follows this pattern:
- `v1` - Current stable version
- `v2` - Development version (when available)

Breaking changes will be introduced in new major versions.

## Support

### Documentation
- [User Guide](USER_GUIDE.md)
- [Deployment Guide](DEPLOYMENT_GUIDE.md)
- [API Reference](API_DOCUMENTATION.md)

### Contact
- **Email:** api@apsdreamhome.com
- **Phone:** +91-XXXXXXXXXX
- **Support Hours:** 9:00 AM - 7:00 PM IST

### Changelog

#### v1.0.0 (Current)
- Initial API release
- Property listings and details
- Inquiry submission
- Favorites management
- Property types and cities
- Advanced filtering and search
- Mobile-optimized responses

#### Upcoming Features
- User authentication
- Advanced analytics
- Bulk operations
- Webhook support
- Real-time notifications

---

*This API documentation is maintained by the APS Dream Home development team. Last updated: October 12, 2025*

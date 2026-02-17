# APS Dream Home Mobile API Documentation

## Overview
The APS Dream Home Mobile API provides RESTful endpoints for mobile applications to interact with the real estate platform. All API responses are in JSON format with proper CORS headers.

## Base URL
```
http://localhost/apsdreamhomefinal/api/
```

## Authentication
Currently, the API does not require authentication for basic operations. User-specific features like favorites require user_id parameter.

## Response Format
All API responses follow this structure:
```json
{
    "success": true/false,
    "data": {...} | "error": "error message",
    "message": "optional message"
}
```

## Endpoints

### 1. Get Property Types
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
            "description": "Modern apartments"
        },
        {
            "id": 2,
            "name": "Villa",
            "description": "Luxury villas"
        }
    ]
}
```

### 2. Get Cities
**Endpoint:** `GET /api/cities`

**Description:** Get all available cities for filtering

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Gorakhpur",
            "state": "Uttar Pradesh"
        },
        {
            "id": 2,
            "name": "Lucknow",
            "state": "Uttar Pradesh"
        }
    ]
}
```

### 3. Get Properties
**Endpoint:** `GET /api/properties`

**Description:** Get properties with pagination and filtering

**Parameters:**
- `page` (optional): Page number (default: 1)
- `limit` (optional): Items per page (default: 10, max: 50)
- `property_type` (optional): Filter by property type
- `city` (optional): Filter by city
- `min_price` (optional): Minimum price filter
- `max_price` (optional): Maximum price filter
- `featured` (optional): Show only featured properties (true/false)

**Response:**
```json
{
    "success": true,
    "data": {
        "properties": [
            {
                "id": 1,
                "title": "Luxury Villa",
                "description": "Beautiful villa",
                "price": 5000000,
                "city": "Gorakhpur",
                "state": "Uttar Pradesh",
                "property_type": "Villa",
                "bedrooms": 3,
                "bathrooms": 2,
                "area_sqft": 2000,
                "images": [...],
                "features": [...]
            }
        ],
        "pagination": {
            "current_page": 1,
            "per_page": 10,
            "total_pages": 5,
            "total_count": 45
        },
        "filters": {
            "property_type": "Villa",
            "city": "Gorakhpur"
        }
    }
}
```

### 4. Get Single Property
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
                "alt": "Living room"
            }
        ],
        "features": [
            {
                "id": 1,
                "name": "Swimming Pool",
                "icon": "fas fa-swimming-pool"
            }
        ]
    }
}
```

### 5. Submit Property Inquiry
**Endpoint:** `POST /api/inquiry/submit`

**Description:** Submit a property inquiry from mobile app

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
    "message": "I'm interested in this property",
    "inquiry_type": "general"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Inquiry submitted successfully",
    "inquiry_id": 123
}
```

### 6. Toggle Property Favorite
**Endpoint:** `POST /api/favorites/toggle`

**Description:** Add or remove property from user's favorites

**Headers:**
```
Content-Type: application/json
```

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
    "is_favorited": true
}
```

### 7. Get User's Favorite Properties
**Endpoint:** `GET /api/favorites`

**Description:** Get all properties favorited by a user

**Parameters:**
- `user_id` (required): User ID

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
            "image": "uploads/properties/1/image1.jpg"
        }
    ]
}
```

## Error Handling

### HTTP Status Codes
- `200` - Success
- `400` - Bad Request (missing required parameters)
- `404` - Not Found (property/user not found)
- `500` - Internal Server Error

### Error Response Format
```json
{
    "success": false,
    "error": "Property not found",
    "message": "The requested property could not be found"
}
```

## CORS Headers
All API endpoints include proper CORS headers:
```
Access-Control-Allow-Origin: *
Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE
Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With
```

## Rate Limiting
Currently, no rate limiting is implemented. Consider implementing for production use.

## Testing

### Using cURL
```bash
# Get property types
curl -X GET "http://localhost/apsdreamhomefinal/api/property-types"

# Get properties with filters
curl -X GET "http://localhost/apsdreamhomefinal/api/properties?property_type=Villa&city=Gorakhpur&page=1&limit=10"

# Submit inquiry
curl -X POST "http://localhost/apsdreamhomefinal/api/inquiry/submit" \
  -H "Content-Type: application/json" \
  -d '{"property_id":1,"name":"John Doe","email":"john@example.com","phone":"+91-9876543210","message":"Interested in this property"}'
```

### Using JavaScript (Fetch API)
```javascript
// Get properties
fetch('http://localhost/apsdreamhomefinal/api/properties?property_type=Villa&page=1&limit=10')
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      console.log('Properties:', data.data.properties);
    } else {
      console.error('Error:', data.error);
    }
  })
  .catch(error => console.error('Network error:', error));

// Submit inquiry
fetch('http://localhost/apsdreamhomefinal/api/inquiry/submit', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    property_id: 1,
    name: 'John Doe',
    email: 'john@example.com',
    phone: '+91-9876543210',
    message: 'Interested in this property'
  })
})
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      console.log('Inquiry submitted:', data.message);
    } else {
      console.error('Error:', data.error);
    }
  })
  .catch(error => console.error('Network error:', error));
```

## Integration Tips

### Mobile App Development
1. **Error Handling:** Always check the `success` field in responses
2. **Loading States:** Show loading indicators during API calls
3. **Offline Support:** Consider caching data for offline viewing
4. **Image Optimization:** Use appropriate image sizes for mobile devices
5. **Pull to Refresh:** Implement pull-to-refresh for property listings

### Performance Optimization
1. **Pagination:** Use pagination for large property lists
2. **Filtering:** Implement client-side filtering where possible
3. **Caching:** Cache property types and cities locally
4. **Image Loading:** Use lazy loading for property images

## Support

For API issues or questions:
- **Email:** support@apsdreamhome.com
- **Documentation:** This document will be updated as the API evolves
- **Version:** API v1.0

## Changelog

### v1.0
- Initial API release
- Property listings with filtering
- Property details
- Inquiry submission
- Favorites management
- Property types and cities endpoints

---

*This API documentation is maintained by APS Dream Home development team.*

# APS Dream Home API Documentation

## Base URL
```
Production: https://yourdomain.com/api
Development: http://localhost/apsdreamhome/api
```

## Authentication
Most endpoints require authentication. Include your API key in the request header:
```
Authorization: Bearer {your-api-key}
```

---

## 📊 Health Check
### GET /api/health
Check API health status.

**Response:**
```json
{
  "status": "healthy",
  "timestamp": "2026-05-17 16:40:48",
  "version": "2.0"
}
```

---

## 🔐 Authentication

### POST /api/auth/login
User login endpoint.

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

**Response:**
```json
{
  "success": true,
  "token": "jwt_token_here",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com",
    "role": "customer"
  }
}
```

### POST /api/auth/logout
User logout endpoint.

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

### POST /api/auth/register
User registration endpoint.

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "user@example.com",
  "phone": "9876543210",
  "password": "password123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Registration successful",
  "user_id": 1
}
```

---

## 🏠 Properties

### GET /api/properties
Get all properties with optional filters.

**Query Parameters:**
- `page` (optional): Page number (default: 1)
- `per_page` (optional): Items per page (default: 20)
- `property_type` (optional): Filter by property type
- `listing_type` (optional): Filter by listing type (buy/rent)
- `min_price` (optional): Minimum price
- `max_price` (optional): Maximum price
- `location` (optional): Location filter
- `sort` (optional): Sort order (price_asc, price_desc, date_asc, date_desc)

**Example Request:**
```
GET /api/properties?page=1&per_page=20&property_type=flat&min_price=1000000&max_price=5000000
```

**Response:**
```json
{
  "success": true,
  "data": {
    "properties": [
      {
        "id": 1,
        "title": "Luxury Apartment",
        "property_type": "flat",
        "listing_type": "buy",
        "price": 2500000,
        "address": "123 Main Street",
        "area_sqft": 1200,
        "description": "Beautiful apartment with modern amenities",
        "status": "approved",
        "created_at": "2026-05-17 10:00:00"
      }
    ],
    "total": 150,
    "page": 1,
    "per_page": 20,
    "total_pages": 8
  }
}
```

### GET /api/properties/{id}
Get specific property details.

**Example Request:**
```
GET /api/properties/1
```

**Response:**
```json
{
  "success": true,
  "property": {
    "id": 1,
    "title": "Luxury Apartment",
    "property_type": "flat",
    "listing_type": "buy",
    "price": 2500000,
    "address": "123 Main Street",
    "area_sqft": 1200,
    "description": "Beautiful apartment with modern amenities",
    "images": [
      {
        "id": 1,
        "image_path": "/uploads/properties/image1.jpg"
      }
    ],
    "user": {
      "id": 5,
      "name": "John Doe",
      "phone": "9876543210"
    }
  }
}
```

### POST /api/properties
Create new property listing.

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "title": "Beautiful Apartment",
  "property_type": "flat",
  "listing_type": "buy",
  "price": 2500000,
  "address": "123 Main Street",
  "area_sqft": 1200,
  "description": "Modern apartment with all amenities",
  "state_id": 1,
  "district_id": 2
}
```

**Response:**
```json
{
  "success": true,
  "message": "Property created successfully",
  "property_id": 1
}
```

---

## 🔍 Advanced Search

### GET /api/search/properties
Advanced property search with multiple filters.

**Query Parameters:**
- `search` (optional): Text search in title, description, address
- `property_type[]` (optional): Array of property types
- `listing_type` (optional): buy/rent
- `min_price` (optional): Minimum price
- `max_price` (optional): Maximum price
- `min_area` (optional): Minimum area in sqft
- `max_area` (optional): Maximum area in sqft
- `state_id` (optional): State ID filter
- `district_id` (optional): District ID filter
- `location` (optional): Text location search
- `bedrooms` (optional): Minimum bedrooms
- `amenities[]` (optional): Array of amenities
- `posted_after` (optional): Date filter
- `posted_before` (optional): Date filter
- `sort` (optional): price_asc, price_desc, area_asc, area_desc, date_asc, date_desc, relevance
- `page` (optional): Page number (default: 1)
- `per_page` (optional): Items per page (default: 20)

**Example Request:**
```
GET /api/search/properties?search=apartment&property_type[]=flat&property_type[]=house&min_price=1000000&max_price=5000000&sort=price_asc&page=1
```

**Response:**
```json
{
  "success": true,
  "data": {
    "results": [...],
    "total": 45,
    "page": 1,
    "per_page": 20,
    "total_pages": 3
  },
  "params": {
    "search": "apartment",
    "property_type": ["flat", "house"],
    "min_price": 1000000,
    "max_price": 5000000,
    "sort": "price_asc"
  }
}
```

### GET /api/search/suggestions
Get search suggestions based on partial input.

**Query Parameters:**
- `q` (required): Search query (minimum 2 characters)
- `limit` (optional): Number of suggestions (default: 10)

**Example Request:**
```
GET /api/search/suggestions?q=ap&limit=10
```

**Response:**
```json
{
  "success": true,
  "suggestions": [
    {
      "suggestion": "Apartment",
      "type": "property_type"
    },
    {
      "suggestion": "Apollo Tower",
      "type": "location"
    },
    {
      "suggestion": "apartment search",
      "type": "recent"
    }
  ]
}
```

### GET /api/search/facets
Get available search facets and filter options.

**Response:**
```json
{
  "success": true,
  "facets": {
    "property_types": ["flat", "house", "shop", "plot", "farmhouse"],
    "listing_types": ["buy", "rent"],
    "price_ranges": {
      "min": 100000,
      "max": 50000000
    },
    "states": [
      {
        "id": 1,
        "name": "Uttar Pradesh"
      }
    ],
    "districts": {
      "Uttar Pradesh": [
        {
          "id": 1,
          "name": "Gorakhpur",
          "state_id": 1
        }
      ]
    }
  }
}
```

### GET /api/search/recent
Get user's recent search history.

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `limit` (optional): Number of recent searches (default: 10)

**Response:**
```json
{
  "success": true,
  "recent_searches": [
    {
      "id": 1,
      "search_term": "apartment gorakhpur",
      "filters": {"property_type": "flat", "min_price": 1000000},
      "created_at": "2026-05-17 16:00:00"
    }
  ]
}
```

### GET /api/search/popular
Get popular/trending searches.

**Response:**
```json
{
  "success": true,
  "popular_searches": [
    {
      "search_term": "apartment gorakhpur",
      "search_count": 150
    },
    {
      "search_term": "budget house",
      "search_count": 98
    }
  ]
}
```

---

## 📍 Locations

### GET /api/locations
Get all locations (states and districts).

**Response:**
```json
{
  "success": true,
  "locations": {
    "states": [
      {
        "id": 1,
        "name": "Uttar Pradesh"
      }
    ],
    "districts": [
      {
        "id": 1,
        "name": "Gorakhpur",
        "state_id": 1
      }
    ]
  }
}
```

### GET /api/locations/state/{id}
Get districts by state ID.

**Example Request:**
```
GET /api/locations/state/1
```

**Response:**
```json
{
  "success": true,
  "districts": [
    {
      "id": 1,
      "name": "Gorakhpur",
      "state_id": 1
    }
  ]
}
```

### GET /api/locations/district/{id}
Get cities by district ID.

**Example Request:**
```
GET /api/locations/district/1
```

**Response:**
```json
{
  "success": true,
  "cities": [
    {
      "id": 1,
      "name": "Gorakhpur City",
      "district_id": 1
    }
  ]
}
```

---

## 📝 Inquiries

### POST /api/contact
Submit general contact inquiry.

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "user@example.com",
  "phone": "9876543210",
  "message": "I am interested in your properties"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Inquiry submitted successfully",
  "inquiry_id": 1
}
```

### POST /api/property-inquiry
Submit inquiry for a specific property.

**Request Body:**
```json
{
  "property_id": 1,
  "name": "John Doe",
  "email": "user@example.com",
  "phone": "9876543210",
  "message": "I am interested in this property"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Property inquiry submitted successfully",
  "inquiry_id": 1
}
```

---

## 📰 Newsletter

### POST /api/newsletter
Subscribe to newsletter.

**Request Body:**
```json
{
  "email": "user@example.com",
  "name": "John Doe"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Newsletter subscription successful"
}
```

---

## 🤖 AI Features

### POST /api/ai/chat
Chat with AI assistant.

**Request Body:**
```json
{
  "message": "I want to buy a 2BHK apartment in Gorakhpur under 30 lakhs",
  "language": "hindi"
}
```

**Response:**
```json
{
  "success": true,
  "response": "I found several 2BHK apartments in Gorakhpur under 30 lakhs. Would you like me to show you the options?",
  "intent": "buy",
  "parameters": {
    "property_type": "flat",
    "location": "Gorakhpur",
    "max_price": 3000000,
    "bedrooms": 2
  }
}
```

### POST /api/gemini/chat
Chat with Gemini AI (alternative AI).

**Request Body:**
```json
{
  "message": "Tell me about the best areas to invest in Gorakhpur",
  "context": "real estate investment"
}
```

**Response:**
```json
{
  "success": true,
  "response": "Based on current market trends...",
  "confidence": 0.85
}
```

---

## 👤 User Management

### GET /api/user/profile
Get user profile.

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com",
    "phone": "9876543210",
    "role": "customer",
    "created_at": "2026-05-01 10:00:00"
  }
}
```

### PUT /api/user/profile
Update user profile.

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "name": "John Doe Updated",
  "phone": "9876543211"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Profile updated successfully"
}
```

---

## 📊 Dashboard APIs

### GET /api/user/dashboard
Get user dashboard data.

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "property_count": 5,
    "inquiry_count": 12,
    "recent_properties": [...],
    "recent_inquiries": [...]
  }
}
```

### GET /api/user/properties
Get user's properties.

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "properties": [...]
}
```

### GET /api/user/inquiries
Get user's inquiries.

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "inquiries": [...]
}
```

---

## 🔔 Notifications

### POST /api/notification
Create notification.

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "type": "property_update",
  "title": "Property Status Updated",
  "message": "Your property has been approved",
  "user_id": 1
}
```

**Response:**
```json
{
  "success": true,
  "message": "Notification created successfully"
}
```

---

## 📱 Mobile API

### POST /api/v2/mobile/auth/login
Mobile app login.

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "password123",
  "device_token": "firebase_device_token"
}
```

**Response:**
```json
{
  "success": true,
  "token": "jwt_token",
  "user": {...},
  "device_registered": true
}
```

### GET /api/v2/mobile/sync
Sync data for mobile app.

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "properties": [...],
    "locations": [...],
    "user_data": {...},
    "sync_timestamp": "2026-05-17 16:40:48"
  }
}
```

---

## ❌ Error Responses

All endpoints may return error responses in case of failure.

### 400 Bad Request
```json
{
  "success": false,
  "error": "Invalid request parameters",
  "code": 400
}
```

### 401 Unauthorized
```json
{
  "success": false,
  "error": "Authentication required",
  "code": 401
}
```

### 403 Forbidden
```json
{
  "success": false,
  "error": "Access denied",
  "code": 403
}
```

### 404 Not Found
```json
{
  "success": false,
  "error": "Resource not found",
  "code": 404
}
```

### 500 Internal Server Error
```json
{
  "success": false,
  "error": "Internal server error",
  "code": 500
}
```

---

## 📏 Rate Limiting

API is rate-limited to prevent abuse:
- **Public endpoints:** 100 requests per hour
- **Authenticated endpoints:** 1000 requests per hour
- **Mobile API:** 500 requests per hour

Rate limit headers are included in responses:
```
X-RateLimit-Limit: 1000
X-RateLimit-Remaining: 995
X-RateLimit-Reset: 1621234567
```

---

## 🔐 Security

### CSRF Protection
All POST/PUT/DELETE requests require CSRF token:
```
X-CSRF-Token: {csrf_token}
```

### Request Signing
Some endpoints require request signature:
```
X-Signature: {hmac_signature}
```

---

## 📈 Performance

### Caching
Most endpoints are cached for performance:
- **Properties API:** 5 minutes
- **Search API:** 5 minutes
- **Locations API:** 24 hours
- **Facets API:** 1 hour

Cache headers are included:
```
Cache-Control: public, max-age=300
ETag: "abc123"
```

### Pagination
List endpoints support pagination:
- Default: 20 items per page
- Maximum: 100 items per page
- Total pages included in response

---

## 🧪 Testing

### Test API Health
```bash
curl -X GET https://yourdomain.com/api/health
```

### Test Authentication
```bash
curl -X POST https://yourdomain.com/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password"}'
```

### Test Properties API
```bash
curl -X GET "https://yourdomain.com/api/properties?page=1&per_page=10"
```

---

## 📞 Support

For API support:
- **Email:** api-support@apsdreamhome.com
- **Documentation:** https://yourdomain.com/docs/api
- **Status Page:** https://status.apsdreamhome.com

---

**API Version:** 2.0  
**Last Updated:** 2026-05-17  
**Base URL:** https://yourdomain.com/api
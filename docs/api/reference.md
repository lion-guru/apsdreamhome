# API Reference

This document provides detailed information about the APS Dream Home API endpoints, request/response formats, and authentication methods.

## Base URL
All API endpoints are relative to the base URL:
```
https://api.apsdreamhome.com/v1
```

## Authentication
API requests are authenticated using Bearer tokens. Include the token in the `Authorization` header:
```
Authorization: Bearer your_api_token_here
```

## Response Format
All API responses are in JSON format with the following structure:
```json
{
    "success": true,
    "message": "Operation completed successfully",
    "data": {
        // Response data
    },
    "meta": {
        // Pagination info (if applicable)
    }
}
```

## Error Handling
Errors return an appropriate HTTP status code and a JSON response:
```json
{
    "success": false,
    "message": "Error description",
    "errors": {
        // Field-specific errors
    }
}
```

## Endpoints

### Authentication

#### Login
```
POST /auth/login
```

**Request Body:**
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
    "data": {
        "token": "api_token_here",
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "user@example.com",
            "role": "agent"
        }
    }
}
```

### Properties

#### List Properties
```
GET /properties
```

**Query Parameters:**
- `status` - Filter by status (available, sold, pending)
- `min_price` - Minimum price
- `max_price` - Maximum price
- `bedrooms` - Number of bedrooms
- `page` - Page number (default: 1)
- `per_page` - Items per page (default: 15)

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "title": "Luxury Villa",
            "price": 500000,
            "bedrooms": 4,
            "bathrooms": 3,
            "area": 2500,
            "status": "available"
        }
    ],
    "meta": {
        "current_page": 1,
        "last_page": 5,
        "per_page": 15,
        "total": 75
    }
}
```

### Leads

#### Create Lead
```
POST /leads
```

**Request Body:**
```json
{
    "property_id": 1,
    "name": "John Smith",
    "email": "john@example.com",
    "phone": "+1234567890",
    "message": "I'm interested in this property"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Lead created successfully",
    "data": {
        "id": 1,
        "status": "new",
        "created_at": "2025-05-18T15:00:00Z"
    }
}
```

### Visits

#### Schedule Visit
```
POST /visits/schedule
```

**Request Body:**
```json
{
    "property_id": 1,
    "visit_date": "2025-06-01",
    "visit_time": "14:30:00",
    "customer_name": "John Smith",
    "customer_email": "john@example.com",
    "customer_phone": "+1234567890"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Visit scheduled successfully",
    "data": {
        "id": 1,
        "visit_date": "2025-06-01",
        "visit_time": "14:30:00",
        "status": "scheduled"
    }
}
```

## Rate Limiting
API requests are limited to 60 requests per minute per IP address. Exceeding this limit will result in a 429 Too Many Requests response.

## Versioning
API versioning is handled through the URL path. The current version is v1.

## Support
For API support, please contact api-support@apsdreamhome.com

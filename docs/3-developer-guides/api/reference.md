# API Reference Guide

## Overview

This document provides comprehensive documentation for the APS Dream Home API, enabling developers to interact with the system programmatically.

## Base URL

All API endpoints are relative to the base URL:
```
https://api.apsdreamhome.com/v1
```

## Authentication

### API Key Authentication
Include your API key in the request header:
```
Authorization: Bearer YOUR_API_KEY
```

### OAuth 2.0
For more secure access, use OAuth 2.0:
1. Register your application
2. Obtain client credentials
3. Request access token
4. Include token in requests

## Rate Limiting

- **Rate Limit**: 100 requests per minute
- **Response Headers**:
  - `X-RateLimit-Limit`: Request limit
  - `X-RateLimit-Remaining`: Remaining requests
  - `X-RateLimit-Reset`: Time when limit resets

## Error Handling

### Standard Error Response
```json
{
  "error": {
    "code": "error_code",
    "message": "Human-readable error message",
    "details": {
      "field1": "Error detail 1",
      "field2": "Error detail 2"
    }
  }
}
```

### Common HTTP Status Codes
- `200 OK`: Successful request
- `201 Created`: Resource created
- `400 Bad Request`: Invalid request
- `401 Unauthorized`: Authentication failed
- `403 Forbidden`: Insufficient permissions
- `404 Not Found`: Resource not found
- `429 Too Many Requests`: Rate limit exceeded
- `500 Internal Server Error`: Server error

## Endpoints

### Properties

#### List Properties
```
GET /properties
```

**Query Parameters**
- `status`: Filter by status (available, sold, etc.)
- `type`: Filter by property type
- `min_price`: Minimum price
- `max_price`: Maximum price
- `bedrooms`: Number of bedrooms
- `bathrooms`: Number of bathrooms
- `sort`: Sort field (price, date, etc.)
- `order`: Sort order (asc, desc)
- `page`: Page number
- `per_page`: Items per page (max 100)

**Example Response**
```json
{
  "data": [
    {
      "id": "prop_123",
      "title": "Luxury Villa with Ocean View",
      "type": "House",
      "status": "available",
      "price": 1250000,
      "bedrooms": 4,
      "bathrooms": 3.5,
      "area": 3200,
      "location": {
        "address": "123 Beachfront Ave",
        "city": "Malibu",
        "state": "CA",
        "zip": "90210",
        "coordinates": {
          "lat": 34.0259,
          "lng": -118.7798
        }
      },
      "images": [
        {
          "url": "https://example.com/images/prop123/1.jpg",
          "is_primary": true
        }
      ],
      "created_at": "2023-01-15T10:30:00Z",
      "updated_at": "2023-01-15T10:30:00Z"
    }
  ],
  "meta": {
    "total": 1,
    "per_page": 20,
    "current_page": 1,
    "last_page": 1
  }
}
```

#### Get Property Details
```
GET /properties/{id}
```

**Path Parameters**
- `id` (required): Property ID

**Example Response**
```json
{
  "data": {
    "id": "prop_123",
    "title": "Luxury Villa with Ocean View",
    "description": "Stunning oceanfront property with panoramic views...",
    "type": "House",
    "status": "available",
    "price": 1250000,
    "bedrooms": 4,
    "bathrooms": 3.5,
    "area": 3200,
    "year_built": 2018,
    "features": [
      "Pool",
      "Garden",
      "Garage"
    ],
    "location": {
      "address": "123 Beachfront Ave",
      "city": "Malibu",
      "state": "CA",
      "zip": "90210",
      "neighborhood": "Malibu Beach",
      "coordinates": {
        "lat": 34.0259,
        "lng": -118.7798
      }
    },
    "images": [
      {
        "url": "https://example.com/images/prop123/1.jpg",
        "is_primary": true,
        "caption": "Front View"
      },
      {
        "url": "https://example.com/images/prop123/2.jpg",
        "is_primary": false,
        "caption": "Living Room"
      }
    ],
    "videos": [
      {
        "url": "https://youtube.com/embed/example123",
        "type": "youtube",
        "thumbnail": "https://img.youtube.com/vi/example123/maxresdefault.jpg"
      }
    ],
    "virtual_tour": {
      "url": "https://my.matterport.com/show/?m=example123",
      "type": "matterport"
    },
    "agent": {
      "id": "agent_456",
      "name": "John Smith",
      "email": "john.smith@apsdreamhome.com",
      "phone": "+15551234567",
      "photo": "https://example.com/agents/john-smith.jpg"
    },
    "created_at": "2023-01-15T10:30:00Z",
    "updated_at": "2023-01-15T10:30:00Z"
  }
}
```

#### Create Property
```
POST /properties
```

**Request Body**
```json
{
  "title": "New Luxury Property",
  "description": "Beautiful property with amazing views...",
  "type": "House",
  "status": "available",
  "price": 1500000,
  "bedrooms": 4,
  "bathrooms": 3.5,
  "area": 3500,
  "year_built": 2020,
  "features": ["Pool", "Garden", "Garage"],
  "location": {
    "address": "456 Ocean Drive",
    "city": "Malibu",
    "state": "CA",
    "zip": "90210",
    "neighborhood": "Malibu Beach",
    "coordinates": {
      "lat": 34.0259,
      "lng": -118.7798
    }
  },
  "agent_id": "agent_456"
}
```

**Response**
```json
{
  "data": {
    "id": "prop_124",
    "message": "Property created successfully"
  }
}
```

#### Update Property
```
PUT /properties/{id}
```

**Path Parameters**
- `id` (required): Property ID

**Request Body**
```json
{
  "status": "under_contract",
  "price": 1450000
}
```

**Response**
```json
{
  "data": {
    "id": "prop_124",
    "message": "Property updated successfully"
  }
}
```

#### Delete Property
```
DELETE /properties/{id}
```

**Path Parameters**
- `id` (required): Property ID

**Response**
```json
{
  "data": {
    "id": "prop_124",
    "message": "Property deleted successfully"
  }
}
```

### Leads

#### List Leads
```
GET /leads
```

**Query Parameters**
- `status`: Filter by status
- `source`: Filter by source
- `agent_id`: Filter by assigned agent
- `start_date`: Filter by creation date (ISO 8601)
- `end_date`: Filter by creation date (ISO 8601)
- `sort`: Sort field (created_at, updated_at, etc.)
- `order`: Sort order (asc, desc)
- `page`: Page number
- `per_page`: Items per page (max 100)

**Example Response**
```json
{
  "data": [
    {
      "id": "lead_789",
      "first_name": "John",
      "last_name": "Doe",
      "email": "john.doe@example.com",
      "phone": "+15551234567",
      "status": "new",
      "source": "website",
      "assigned_agent_id": "agent_456",
      "created_at": "2023-01-15T10:30:00Z",
      "updated_at": "2023-01-15T10:30:00Z"
    }
  ],
  "meta": {
    "total": 1,
    "per_page": 20,
    "current_page": 1,
    "last_page": 1
  }
}
```

#### Get Lead Details
```
GET /leads/{id}
```

**Path Parameters**
- `id` (required): Lead ID

**Example Response**
```json
{
  "data": {
    "id": "lead_789",
    "first_name": "John",
    "last_name": "Doe",
    "email": "john.doe@example.com",
    "phone": "+15551234567",
    "address": {
      "street": "123 Main St",
      "city": "Los Angeles",
      "state": "CA",
      "zip": "90001",
      "country": "USA"
    },
    "status": "new",
    "source": "website",
    "budget_min": 1000000,
    "budget_max": 1500000,
    "preferred_locations": ["Malibu", "Santa Monica"],
    "property_types": ["House", "Condo"],
    "bedrooms_min": 3,
    "bathrooms_min": 2,
    "notes": "Interested in properties with ocean views",
    "assigned_agent_id": "agent_456",
    "created_at": "2023-01-15T10:30:00Z",
    "updated_at": "2023-01-15T10:30:00Z",
    "activities": [
      {
        "id": "act_123",
        "type": "note",
        "content": "Initial inquiry received",
        "created_at": "2023-01-15T10:30:00Z",
        "created_by": "system"
      }
    ]
  }
}
```

### Visits

#### Schedule Visit
```
POST /visits
```

**Request Body**
```json
{
  "property_id": "prop_123",
  "lead_id": "lead_789",
  "scheduled_at": "2023-02-01T14:00:00Z",
  "duration": 60,
  "type": "in_person",
  "notes": "Client is interested in the ocean view"
}
```

**Response**
```json
{
  "data": {
    "id": "visit_456",
    "message": "Visit scheduled successfully",
    "confirmation_url": "https://api.apsdreamhome.com/visits/visit_456/confirm"
  }
}
```

#### List Visits
```
GET /visits
```

**Query Parameters**
- `status`: Filter by status (scheduled, confirmed, completed, cancelled)
- `property_id`: Filter by property
- `lead_id`: Filter by lead
- `agent_id`: Filter by agent
- `start_date`: Filter by start date (ISO 8601)
- `end_date`: Filter by end date (ISO 8601)
- `sort`: Sort field (scheduled_at, created_at, etc.)
- `order`: Sort order (asc, desc)
- `page`: Page number
- `per_page`: Items per page (max 100)

**Example Response**
```json
{
  "data": [
    {
      "id": "visit_456",
      "property_id": "prop_123",
      "lead_id": "lead_789",
      "agent_id": "agent_456",
      "scheduled_at": "2023-02-01T14:00:00Z",
      "duration": 60,
      "type": "in_person",
      "status": "scheduled",
      "notes": "Client is interested in the ocean view",
      "created_at": "2023-01-20T09:15:00Z",
      "updated_at": "2023-01-20T09:15:00Z"
    }
  ],
  "meta": {
    "total": 1,
    "per_page": 20,
    "current_page": 1,
    "last_page": 1
  }
}
```

## Webhooks

### Available Webhooks
- `property.created`: New property added
- `property.updated`: Property updated
- `property.deleted`: Property deleted
- `lead.created`: New lead created
- `lead.updated`: Lead updated
- `lead.status_changed`: Lead status changed
- `visit.scheduled`: Visit scheduled
- `visit.confirmed`: Visit confirmed
- `visit.cancelled`: Visit cancelled
- `visit.completed`: Visit completed

### Setting Up Webhooks
1. Go to Developer Settings
2. Click "Add Webhook"
3. Enter endpoint URL
4. Select events to subscribe to
5. Set secret for signing
6. Save webhook

### Webhook Payload
```json
{
  "event": "property.created",
  "data": {
    "id": "prop_123",
    "title": "Luxury Villa with Ocean View",
    "type": "House",
    "status": "available",
    "price": 1250000,
    "created_at": "2023-01-15T10:30:00Z"
  },
  "timestamp": "2023-01-15T10:30:01Z"
}
```

### Webhook Security
- Each webhook includes an `X-Signature` header
- Signature is generated using HMAC-SHA256
- Verify signatures to ensure webhook authenticity

## SDKs

### Official SDKs
- **JavaScript/Node.js**: `npm install aps-dreamhome-sdk`
- **Python**: `pip install aps-dreamhome`
- **PHP**: `composer require aps-dreamhome/api`
- **Ruby**: `gem install aps-dreamhome`

### Example (JavaScript)
```javascript
const APSDreamHome = require('aps-dreamhome-sdk');

const client = new APSDreamHome({
  apiKey: 'your_api_key',
  environment: 'production' // or 'sandbox'
});

// List properties
client.properties.list({
  status: 'available',
  min_price: 1000000,
  max_price: 2000000,
  bedrooms: 3,
  sort: 'price',
  order: 'asc',
  page: 1,
  per_page: 10
}).then(properties => {
  console.log(properties);
}).catch(error => {
  console.error(error);
});

// Create a lead
client.leads.create({
  first_name: 'John',
  last_name: 'Doe',
  email: 'john.doe@example.com',
  phone: '+15551234567',
  status: 'new',
  source: 'website',
  budget_min: 1000000,
  budget_max: 1500000,
  preferred_locations: ['Malibu', 'Santa Monica'],
  property_types: ['House'],
  bedrooms_min: 3,
  bathrooms_min: 2,
  notes: 'Interested in properties with ocean views'
}).then(lead => {
  console.log('Lead created:', lead);
}).catch(error => {
  console.error('Error creating lead:', error);
});
```

## Changelog

### v1.0.0 (2023-01-01)
- Initial release
- Properties management
- Leads management
- Visits scheduling
- Webhooks support

### v1.1.0 (2023-02-15)
- Added pagination to all list endpoints
- Added filtering by multiple statuses
- Improved error messages
- Added rate limiting headers

### v1.2.0 (2023-03-10)
- Added bulk operations
- Enhanced search capabilities
- Added sorting options
- Performance improvements

## Support

For API support, please contact:
- Email: api-support@apsdreamhome.com
- Documentation: [https://developer.apsdreamhome.com](https://developer.apsdreamhome.com)
- Status: [https://status.apsdreamhome.com](https://status.apsdreamhome.com)

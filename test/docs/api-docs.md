# APS Dream Home API Documentation

## Overview

The APS Dream Home API provides comprehensive REST endpoints for managing real estate properties, leads, and related data. This documentation covers all available endpoints, their parameters, responses, and authentication requirements.

**Base URL:** `http://localhost/apsdreamhome/api/`

**Authentication:** Bearer token or API key (required for most endpoints)

---

## Authentication

### API Key Authentication
Most endpoints require an API key to be passed in the request headers or as a query parameter.

```
Authorization: Bearer your-api-key
# OR
?api_key=your-api-key
```

### User Authentication
Some endpoints require user authentication (marked with 🔐).

---

## Endpoints

### 🔍 Health Check

#### GET `/health`
Check API availability and status.

**Response:**
```json
{
  "status": "ok",
  "message": "API is running"
}
```

---

## 🏠 Properties API

### GET `/properties`
Search and filter properties with pagination.

**Parameters:**
- `keyword` (string): Search term
- `location` (string): Location filter
- `type` (string): Property type
- `purpose` (string): Sale/rent
- `min_price` (number): Minimum price
- `max_price` (number): Maximum price
- `bedrooms` (number): Number of bedrooms
- `bathrooms` (number): Number of bathrooms
- `page` (number): Page number (default: 1)
- `limit` (number): Items per page (default: 10, max: 50)

**Response:**
```json
{
  "success": true,
  "data": {
    "properties": [...],
    "pagination": {
      "current_page": 1,
      "total_pages": 5,
      "total_items": 47,
      "per_page": 10
    }
  }
}
```

### POST `/properties/bulk/delete` 🔐
Delete multiple properties at once.

**Request Body:**
```json
{
  "ids": [1, 2, 3, 5]
}
```

**Response:**
```json
{
  "success": true,
  "message": "Successfully deleted 3 out of 4 properties",
  "deleted_count": 3,
  "total_requested": 4
}
```

### POST `/properties/bulk/update` 🔐
Update multiple properties at once.

**Request Body:**
```json
{
  "ids": [1, 2, 3],
  "updates": {
    "status": "sold",
    "price": 1500000,
    "featured": true
  }
}
```

**Response:**
```json
{
  "success": true,
  "message": "Successfully updated 3 out of 3 properties",
  "updated_count": 3,
  "total_requested": 3,
  "updated_fields": ["status", "price", "featured"]
}
```

---

## 👥 Leads API

All lead endpoints require authentication (🔐).

### GET `/leads`
Get all leads with filtering and pagination.

**Parameters:**
- `search` (string): Search term
- `status` (string): Lead status
- `source` (string): Lead source
- `assigned_to` (number): Assigned user ID
- `tag` (string): Tag name
- `date_from` (date): Start date
- `date_to` (date): End date
- `per_page` (number): Items per page
- `page` (number): Page number

**Response:**
```json
{
  "success": true,
  "data": {
    "leads": [...],
    "pagination": {...}
  }
}
```

### POST `/leads`
Create a new lead.

**Request Body:**
```json
{
  "first_name": "John",
  "last_name": "Doe",
  "email": "john@example.com",
  "phone": "+91-9876543210",
  "company": "ABC Corp",
  "source": "website",
  "notes": "Interested in 3BHK apartment"
}
```

### GET `/leads/{id}`
Get a specific lead by ID.

### PUT `/leads/{id}`
Update a lead.

### DELETE `/leads/{id}`
Delete a lead.

### PUT `/leads/{id}/assign`
Assign a lead to a user.

**Request Body:**
```json
{
  "user_id": 5,
  "notes": "Assigned to senior agent"
}
```

### PUT `/leads/{id}/status`
Update lead status.

**Request Body:**
```json
{
  "status": "qualified",
  "notes": "Lead qualified after phone call"
}
```

---

## 📊 Lead Management

### POST `/leads/bulk/delete` 🔐
Delete multiple leads.

### POST `/leads/bulk/status` 🔐
Update status for multiple leads.

### POST `/leads/bulk/assign` 🔐
Assign multiple leads to a user.

**Request Body:**
```json
{
  "lead_ids": [1, 2, 3],
  "user_id": 5,
  "notes": "Bulk assignment to new agent"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Successfully assigned 3 out of 3 leads",
  "data": {
    "assigned_count": 3,
    "total_requested": 3,
    "errors": []
  }
}
```

---

## 📈 Statistics & Analytics

### GET `/leads/stats/overview` 🔐
Get overall lead statistics.

### GET `/leads/stats/status` 🔐
Leads grouped by status.

### GET `/leads/stats/source` 🔐
Leads grouped by source.

### GET `/leads/stats/assigned-to` 🔐
Leads grouped by assigned user.

### GET `/leads/stats/created-by` 🔐
Leads grouped by creator.

### GET `/leads/stats/timeline` 🔐
Lead creation timeline data.

---

## 🔍 Lookup Data

### GET `/lookup/statuses` 🔐
Get available lead statuses.

### GET `/lookup/sources` 🔐
Get available lead sources.

### GET `/lookup/tags` 🔐
Get all available tags.

### GET `/lookup/users` 🔐
Get list of users for assignment.

### GET `/lookup/custom-fields` 🔐
Get custom field definitions.

### GET `/lookup/deal-stages` 🔐
Get deal stages for CRM.

---

## 📁 File Management

### GET `/leads/{id}/files` 🔐
Get files attached to a lead.

### POST `/leads/{id}/files` 🔐
Upload a file to a lead.

### DELETE `/leads/{id}/files/{fileId}` 🔐
Delete a file from a lead.

### GET `/leads/{fileId}/download` 🔐
Download a lead file.

---

## 🏷️ Tag Management

### GET `/leads/{id}/tags` 🔐
Get tags for a lead.

### POST `/leads/{id}/tags` 🔐
Add a tag to a lead.

### DELETE `/leads/{id}/tags/{tagId}` 🔐
Remove a tag from a lead.

---

## 📝 Notes Management

### GET `/leads/{id}/notes` 🔐
Get notes for a lead.

### POST `/leads/{id}/notes` 🔐
Add a note to a lead.

### PUT `/leads/{id}/notes/{noteId}` 🔐
Update a note.

### DELETE `/leads/{id}/notes/{noteId}` 🔐
Delete a note.

---

## 📊 Activities

### GET `/leads/{id}/activities` 🔐
Get activity log for a lead.

---

## 💼 Deal Management

### GET `/leads/{id}/deals` 🔐
Get deals for a lead.

### POST `/leads/{id}/deals` 🔐
Create a deal for a lead.

### PUT `/leads/{id}/deals/{dealId}` 🔐
Update a deal.

### DELETE `/leads/{id}/deals/{dealId}` 🔐
Delete a deal.

---

## 🎯 Custom Fields

### GET `/leads/{id}/custom-fields` 🔐
Get custom fields for a lead.

### POST `/leads/{id}/custom-fields` 🔐
Update custom field values.

---

## 📊 Response Format

All API responses follow this structure:

**Success Response:**
```json
{
  "success": true,
  "data": {...},
  "message": "Optional success message"
}
```

**Error Response:**
```json
{
  "success": false,
  "message": "Error description",
  "errors": ["Detailed error messages"]
}
```

---

## 🚫 Error Codes

- `400` - Bad Request (invalid parameters)
- `401` - Unauthorized (invalid API key)
- `403` - Forbidden (insufficient permissions)
- `404` - Not Found (resource doesn't exist)
- `422` - Unprocessable Entity (validation failed)
- `500` - Internal Server Error

---

## 📝 Rate Limiting

- API endpoints are rate limited
- Default: 100 requests per minute per API key
- Contact administrator for higher limits

---

## 🔧 SDK & Examples

### JavaScript Example
```javascript
// Search properties
const response = await fetch('/api/properties?location=Gorakhpur&type=apartment', {
  headers: {
    'Authorization': 'Bearer your-api-key'
  }
});
const data = await response.json();
```

### PHP Example
```php
// Create a lead
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, '/api/leads');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
  'first_name' => 'John',
  'email' => 'john@example.com'
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
  'Content-Type: application/json',
  'Authorization: Bearer your-api-key'
]);
$result = curl_exec($ch);
```

---

## 📞 Support

For API support or questions:
- Email: api@apsdreamhome.com
- Documentation: https://apsdreamhome.com/api-docs
- Status Page: https://status.apsdreamhome.com

---

## 🔄 Changelog

### Version 1.0.0 (Current)
- Initial API release
- Full CRUD operations for leads and properties
- Bulk operations support
- Statistics and analytics
- File management
- Tag and note management

---

*Last Updated: March 2, 2026*
*API Version: 1.0.0*

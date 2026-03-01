# APS Dream Home - API Documentation

## Overview

The APS Dream Home API provides programmatic access to the real estate platform, enabling developers to integrate property data, user management, and transaction processing into their applications.

**Base URL:** `https://yourdomain.com/api/v1`  
**Authentication:** Bearer Token (JWT)  
**Content-Type:** `application/json`  
**Rate Limit:** 1000 requests per hour

---

## 📋 Table of Contents
- [Authentication](#authentication)
- [Properties API](#properties-api)
- [Users API](#users-api)
- [Leads API](#leads-api)
- [Projects API](#projects-api)
- [Payments API](#payments-api)
- [Analytics API](#analytics-api)
- [Webhooks](#webhooks)
- [Error Handling](#error-handling)
- [Rate Limiting](#rate-limiting)

---

## 🔐 Authentication

### Login
Authenticate a user and receive an access token.

```http
POST /api/v1/auth/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "securepassword"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "user@example.com",
      "role": "customer"
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...",
    "expires_in": 3600
  }
}
```

### Register
Create a new user account.

```http
POST /api/v1/auth/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "user@example.com",
  "password": "securepassword",
  "password_confirmation": "securepassword",
  "phone": "+91-9876543210",
  "role": "customer"
}
```

### Refresh Token
Refresh an expired access token.

```http
POST /api/v1/auth/refresh
Authorization: Bearer {token}
```

### Logout
Invalidate the current access token.

```http
POST /api/v1/auth/logout
Authorization: Bearer {token}
```

---

## 🏠 Properties API

### List Properties
Get a paginated list of properties with optional filters.

```http
GET /api/v1/properties?page=1&per_page=20&city=gorakhpur&type=apartment&min_price=1000000&max_price=5000000
Authorization: Bearer {token}
```

**Query Parameters:**
- `page`: Page number (default: 1)
- `per_page`: Items per page (default: 20, max: 100)
- `city`: Filter by city
- `type`: Filter by property type (apartment, villa, commercial, plot)
- `min_price`: Minimum price filter
- `max_price`: Maximum price filter
- `bedrooms`: Number of bedrooms
- `status`: Property status (available, sold, rented)
- `featured`: Show only featured properties (true/false)
- `search`: Keyword search in title and description

**Response:**
```json
{
  "success": true,
  "data": {
    "properties": [
      {
        "id": 1,
        "title": "Luxury 3BHK Apartment",
        "slug": "luxury-3bhk-apartment",
        "description": "Beautiful apartment with modern amenities",
        "price": 4500000,
        "property_type": "apartment",
        "bedrooms": 3,
        "bathrooms": 2,
        "area": 1200,
        "area_unit": "sqft",
        "city": "Gorakhpur",
        "state": "Uttar Pradesh",
        "address": "Civil Lines, Gorakhpur",
        "latitude": 26.7606,
        "longitude": 83.3732,
        "status": "available",
        "featured": true,
        "images": [
          {
            "id": 1,
            "url": "https://yourdomain.com/storage/properties/1/image1.jpg",
            "is_primary": true
          }
        ],
        "agent": {
          "id": 1,
          "name": "Rajesh Kumar",
          "phone": "+91-9876543210",
          "email": "rajesh@apsdreamhome.com"
        },
        "created_at": "2024-01-15T10:30:00Z"
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 20,
      "total": 150,
      "last_page": 8,
      "from": 1,
      "to": 20
    }
  }
}
```

### Get Property Details
Get detailed information about a specific property.

```http
GET /api/v1/properties/{id}
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "property": {
      "id": 1,
      "title": "Luxury 3BHK Apartment",
      "description": "Complete property details...",
      "specifications": {
        "furnished": "semi-furnished",
        "parking": true,
        "lift": true,
        "security": true,
        "power_backup": true
      },
      "amenities": ["swimming pool", "gym", "garden"],
      "nearby_locations": ["school", "hospital", "market"],
      "virtual_tour_url": "https://virtualtour.com/tour123"
    }
  }
}
```

### Create Property (Agent Only)
Create a new property listing.

```http
POST /api/v1/properties
Authorization: Bearer {token}
Content-Type: application/json

{
  "title": "New Luxury Apartment",
  "description": "Beautiful new apartment for sale",
  "price": 5000000,
  "property_type": "apartment",
  "bedrooms": 3,
  "bathrooms": 2,
  "area": 1400,
  "area_unit": "sqft",
  "city": "Lucknow",
  "state": "Uttar Pradesh",
  "address": "Gomti Nagar, Lucknow",
  "latitude": 26.8467,
  "longitude": 80.9462,
  "status": "available",
  "featured": false,
  "furnished": "unfurnished",
  "parking": true,
  "lift": true,
  "security": true,
  "amenities": ["swimming pool", "gym"],
  "images": [
    {"url": "https://example.com/image1.jpg", "is_primary": true},
    {"url": "https://example.com/image2.jpg", "is_primary": false}
  ]
}
```

### Update Property (Agent Only)
Update an existing property.

```http
PUT /api/v1/properties/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "title": "Updated Apartment Title",
  "price": 5500000,
  "status": "sold"
}
```

### Delete Property (Agent Only)
Delete a property listing.

```http
DELETE /api/v1/properties/{id}
Authorization: Bearer {token}
```

---

## 👥 Users API

### Get User Profile
Get current user's profile information.

```http
GET /api/v1/user/profile
Authorization: Bearer {token}
```

### Update Profile
Update user profile information.

```http
PUT /api/v1/user/profile
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Updated Name",
  "phone": "+91-9876543210",
  "avatar": "https://example.com/avatar.jpg"
}
```

### Change Password
Change user password.

```http
PUT /api/v1/user/password
Authorization: Bearer {token}
Content-Type: application/json

{
  "current_password": "oldpassword",
  "password": "newpassword",
  "password_confirmation": "newpassword"
}
```

### Get User Properties (Agent)
Get properties listed by the current agent.

```http
GET /api/v1/user/properties
Authorization: Bearer {token}
```

---

## 📞 Leads API

### Create Lead
Create a new lead/enquiry.

```http
POST /api/v1/leads
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Rajesh Sharma",
  "email": "rajesh@example.com",
  "phone": "+91-9876543210",
  "message": "I'm interested in this property",
  "property_id": 1,
  "source": "website"
}
```

### Get User Leads
Get leads created by the current user.

```http
GET /api/v1/leads
Authorization: Bearer {token}
```

### Update Lead Status (Agent/Admin)
Update the status of a lead.

```http
PUT /api/v1/leads/{id}/status
Authorization: Bearer {token}
Content-Type: application/json

{
  "status": "qualified",
  "notes": "Customer is very interested"
}
```

---

## 🏗️ Projects API

### List Projects
Get a list of real estate projects.

```http
GET /api/v1/projects
Authorization: Bearer {token}
```

### Get Project Details
Get detailed information about a specific project.

```http
GET /api/v1/projects/{id}
Authorization: Bearer {token}
```

---

## 💳 Payments API

### Initiate Payment
Create a payment order for Razorpay.

```http
POST /api/v1/payments/initiate
Authorization: Bearer {token}
Content-Type: application/json

{
  "amount": 100000,
  "currency": "INR",
  "property_id": 1,
  "description": "Property booking payment"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "order_id": "order_xyz123",
    "amount": 100000,
    "currency": "INR",
    "razorpay_key": "rzp_test_xxx",
    "prefill": {
      "name": "John Doe",
      "email": "john@example.com",
      "contact": "+91-9876543210"
    }
  }
}
```

### Verify Payment
Verify payment completion.

```http
POST /api/v1/payments/verify
Authorization: Bearer {token}
Content-Type: application/json

{
  "razorpay_payment_id": "pay_xxx",
  "razorpay_order_id": "order_xyz123",
  "razorpay_signature": "signature_xxx"
}
```

### Get Payment History
Get user's payment history.

```http
GET /api/v1/payments/history
Authorization: Bearer {token}
```

---

## 📊 Analytics API

### Get Dashboard Stats (Agent)
Get agent's dashboard statistics.

```http
GET /api/v1/analytics/dashboard
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "total_properties": 25,
    "active_properties": 20,
    "total_leads": 150,
    "new_leads_this_month": 45,
    "conversion_rate": 15.5,
    "total_earnings": 225000,
    "pending_commission": 45000
  }
}
```

### Get Property Performance
Get performance metrics for a specific property.

```http
GET /api/v1/analytics/properties/{id}
Authorization: Bearer {token}
```

---

## 🪝 Webhooks

### Payment Webhook
Handle payment gateway webhooks.

```http
POST /api/v1/webhooks/razorpay
X-Razorpay-Signature: {signature}
Content-Type: application/json

{
  "event": "payment.captured",
  "payload": {
    "payment": {
      "id": "pay_xxx",
      "amount": 100000,
      "currency": "INR",
      "status": "captured"
    }
  }
}
```

### Supported Webhook Events
- `payment.captured` - Payment successfully completed
- `payment.failed` - Payment failed
- `payment.authorized` - Payment authorized but not captured
- `order.paid` - Order payment completed

---

## ⚠️ Error Handling

### Standard Error Response
```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "The given data was invalid",
    "details": {
      "email": ["The email field is required"],
      "password": ["The password must be at least 8 characters"]
    }
  }
}
```

### HTTP Status Codes
- `200` - Success
- `201` - Created
- `400` - Bad Request (validation errors)
- `401` - Unauthorized (invalid/missing token)
- `403` - Forbidden (insufficient permissions)
- `404` - Not Found
- `422` - Unprocessable Entity (validation failed)
- `429` - Too Many Requests (rate limit exceeded)
- `500` - Internal Server Error

### Common Error Codes
- `VALIDATION_ERROR` - Input validation failed
- `UNAUTHORIZED` - Authentication required
- `FORBIDDEN` - Insufficient permissions
- `NOT_FOUND` - Resource not found
- `RATE_LIMIT_EXCEEDED` - Too many requests
- `PAYMENT_FAILED` - Payment processing failed
- `PROPERTY_NOT_AVAILABLE` - Property no longer available

---

## 🚦 Rate Limiting

### Limits
- **Authenticated Users**: 1000 requests per hour
- **Unauthenticated Users**: 100 requests per hour
- **Payment Endpoints**: 50 requests per hour

### Rate Limit Headers
```http
X-RateLimit-Limit: 1000
X-RateLimit-Remaining: 999
X-RateLimit-Reset: 1609459200
Retry-After: 3600
```

### Handling Rate Limits
When rate limit is exceeded, the API returns:
```json
{
  "success": false,
  "error": {
    "code": "RATE_LIMIT_EXCEEDED",
    "message": "Too many requests",
    "retry_after": 3600
  }
}
```

---

## 🔧 SDKs & Libraries

### PHP SDK
```php
use APSDreamHome\API\Client;

$client = new Client('your-api-key');
$properties = $client->properties()->list(['city' => 'gorakhpur']);
```

### JavaScript SDK
```javascript
import { APSDreamHomeAPI } from 'apsdreamhome-api';

const api = new APSDreamHomeAPI({ apiKey: 'your-api-key' });
const properties = await api.properties.list({ city: 'gorakhpur' });
```

### Mobile SDKs
- **Android**: Available on Maven Central
- **iOS**: Available on CocoaPods
- **React Native**: NPM package available

---

## 🔒 Security

### API Key Management
- Keep API keys secure and never expose in client-side code
- Rotate keys regularly
- Use different keys for different environments
- Monitor API usage and set up alerts

### Data Encryption
- All API communications use HTTPS with TLS 1.2+
- Sensitive data is encrypted at rest
- Payment information is PCI DSS compliant

### Authentication Security
- JWT tokens expire after 1 hour
- Refresh tokens for seamless authentication
- Secure password hashing with bcrypt
- Two-factor authentication support

---

## 📞 Support

### Developer Support
- **Documentation**: https://docs.apsdreamhome.com
- **API Status**: https://status.apsdreamhome.com
- **Developer Portal**: https://developer.apsdreamhome.com
- **Community Forums**: https://community.apsdreamhome.com

### Contact Information
- **Email**: api-support@apsdreamhome.com
- **Developer Chat**: Slack workspace available
- **Issue Tracker**: GitHub repository for bug reports

---

## 📝 Changelog

### Version 1.0.0 (Current)
- Initial API release
- Core property, user, and lead management
- Payment integration with Razorpay
- Basic analytics and reporting

### Upcoming Features
- **v1.1.0**: Advanced search filters, bulk operations
- **v1.2.0**: Real-time notifications, chat integration
- **v1.3.0**: Advanced analytics, custom reports
- **v2.0.0**: GraphQL API, enhanced mobile support

---

**For the latest API documentation and updates, visit [https://docs.apsdreamhome.com/api](https://docs.apsdreamhome.com/api)**

**Last updated: February 2026**

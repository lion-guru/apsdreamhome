# APS Dream Home - API Documentation (v1)

This document provides an overview of the available API endpoints for the APS Dream Home platform.

## Base URL
`https://yourdomain.com/apsdreamhome/api/v1`

## Authentication
Most endpoints require a JWT token or an active session.
- **Login**: `POST /auth/login`
- **Me**: `GET /auth/me` (Returns current user info)

---

## Properties
Manage real estate listings.

### List Properties
`GET /properties`
- **Parameters**:
  - `status`: `available`, `sold` (default: `available`)
  - `featured`: `1` or `0`
  - `page`: Page number
  - `per_page`: Items per page
- **Response**: Paginated list of properties.
- **Caching**: 30 minutes.

### Get Property Detail
`GET /properties/{id}`
- **Response**: Detailed property object.
- **Caching**: 60 minutes.

### Create Property
`POST /properties` (Requires `create_properties` permission)
- **Body**: JSON object with property details.

---

## Referrals
Track and manage agent referrals.

### Create Referral
`POST /referrals`
- **Body**: `{ "email": "friend@example.com" }`
- **Response**: Referral object with share link.

### Referral Stats
`GET /referrals/stats`
- **Response**: `{ "total": 10, "converted": 2 }`

---

## Notifications
Push notification management.

### Register Device
`POST /notifications/register`
- **Body**: `{ "token": "PUSH_TOKEN", "platform": "web|android|ios" }`

### Unregister Device
`POST /notifications/unregister`
- **Body**: `{ "token": "PUSH_TOKEN" }`

### Send Test (Admin Only)
`POST /notifications/test`
- **Body**: `{ "user_id": 123, "title": "Hello", "message": "World" }`

---

## SEO
Fetch metadata for dynamic pages.

### Get Metadata
`GET /seo/metadata?url={url}`
- **Response**: `{ "title": "...", "description": "...", "og_image": "..." }`
- **Caching**: 24 hours.

---

## Sharing
Track property shares and clicks.

### Track Click
`POST /sharing/track`
- **Body**: `{ "property_id": 123, "platform": "whatsapp|facebook" }`

### Generate Link
`GET /sharing/generate?property_id=123`
- **Response**: Shareable URL.

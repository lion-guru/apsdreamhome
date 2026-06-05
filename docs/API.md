# APS Dream Home – API Reference

Complete reference for the APS Dream Home API: web routes, REST API endpoints, the mobile JWT API, the WebSocket protocol, webhooks, rate limits, and error codes.

> **Base URL** (production): `https://apsdreamhome.com`
> **Base URL** (local dev):  `http://localhost/apsdreamhome`
> **API version**: v2 (current) – `/api/v2/...`
> **Auth**: Session cookies (web), Bearer tokens (API), JWT (mobile)

---

## Table of Contents

1. [Authentication Modes](#authentication-modes)
2. [Web Routes (Public)](#web-routes-public)
3. [REST API – `/api/*`](#rest-api)
4. [Mobile API – `/api/mobile/*`](#mobile-api)
5. [WebSocket Protocol](#websocket-protocol)
6. [Webhook Signature Verification](#webhook-signature-verification)
7. [Rate Limits](#rate-limits)
8. [Standard Error Codes](#standard-error-codes)
9. [Pagination](#pagination)
10. [Date / Time Formats](#date--time-formats)

---

## Authentication Modes

| Mode | Used For | Header / Cookie |
|------|----------|-----------------|
| **Session cookie** | Web browser (customer + admin) | `PHPSESSID=...` |
| **Bearer token** | Server-to-server API access | `Authorization: Bearer <api_key>:<api_secret>` |
| **JWT** | Mobile apps | `Authorization: Bearer <jwt>` |
| **CSRF token** | All write methods (web) | `X-CSRF-Token` header **or** `_token` form field |
| **Cron secret** | Cron endpoints | `?key=<CRON_SECRET>` query string |
| **Webhook HMAC** | Webhook receivers verify us | `X-Webhook-Signature` header |

---

## Web Routes (Public)

These are HTML-rendering routes. All return `text/html`.

### Customer-Facing

| Route | Method | Purpose |
|-------|--------|---------|
| `/` | GET | Homepage |
| `/properties` | GET | Property listing with filters |
| `/properties/{id}` | GET | Property detail page |
| `/list-property` | GET, POST | Submit a new property |
| `/projects` | GET | All colony / project listings |
| `/projects/{location}` | GET | Projects filtered by city |
| `/about` | GET | About page |
| `/contact` | GET, POST | Contact form |
| `/services` | GET | Services page |
| `/blog` | GET | Blog index |
| `/blog/{slug}` | GET | Blog post |
| `/news` | GET | News listing |
| `/faqs` | GET | FAQ page |
| `/testimonials` | GET | Testimonials |
| `/careers` | GET | Career listings |
| `/privacy` | GET | Privacy policy |
| `/legal/terms-conditions` | GET | T&C |
| `/login` | GET, POST | Customer login |
| `/register` | GET, POST | Customer register |
| `/forgot-password` | GET, POST | Password reset request |
| `/reset-password` | GET, POST | Password reset complete |
| `/logout` | GET | Customer logout |
| `/language/set/{lang}` | GET | Switch language (en / hi) |

### Customer Dashboard (login required)

| Route | Method | Purpose |
|-------|--------|---------|
| `/user/dashboard` | GET | Main dashboard |
| `/user/profile` | GET, POST | Profile management |
| `/user/properties` | GET | My properties |
| `/user/inquiries` | GET | My inquiries |
| `/user/favorites` | GET | Saved favorites |
| `/user/saved-searches` | GET, POST | List + create saved searches |
| `/user/saved-searches/{id}` | PUT, DELETE | Update / delete |
| `/user/saved-searches/{id}/run` | GET | Execute saved search |
| `/user/saved-searches/{id}/alerts` | POST | Toggle email alerts |
| `/user/saved-searches/manage-alerts` | GET | Alert subscription page |
| `/user/bank-details` | GET, POST | Manage bank info |
| `/user/network` | GET | MLM downline (if associate) |
| `/user/bookings` | GET | My bookings |
| `/user/bookings/{id}` | GET | Booking detail |
| `/user/two-factor` | GET, POST | 2FA setup |
| `/visits/book` | POST | Book a property visit |
| `/visits/my-visits` | GET | My visits |
| `/visits/cancel` | POST | Cancel a visit |
| `/property-comparison` | GET, POST, DELETE | Compare properties |
| `/property-alerts/subscribe` | GET, POST | Subscribe to alerts |
| `/property-alerts/unsubscribe` | GET | Unsubscribe link |

### Admin Panel (admin auth required)

All admin routes are under `/admin/*`. Full list is at `routes/web.php`.

Key examples:

| Route | Method | Purpose |
|-------|--------|---------|
| `/admin/login` | GET, POST | Admin login |
| `/admin/dashboard` | GET | Main admin dashboard |
| `/admin/users` | GET | User management |
| `/admin/users/{id}` | GET, POST, DELETE | User detail / update / delete |
| `/admin/user-properties` | GET | Pending properties |
| `/admin/user-properties/action` | POST | Approve / reject |
| `/admin/bookings` | GET | Bookings list |
| `/admin/bookings/{id}` | GET | Booking detail |
| `/admin/leads` | GET | Lead list |
| `/admin/lead-kanban` | GET | Drag-drop lead pipeline |
| `/admin/colonies` | GET, POST | Colony management |
| `/admin/plots` | GET, POST | Plot management |
| `/admin/finance` | GET | Finance dashboard |
| `/admin/invoices` | GET | Invoice list |
| `/admin/commissions` | GET | Commission tracking |
| `/admin/payouts` | GET | Payouts |
| `/admin/hr/users` | GET | Employee list |
| `/admin/hrm/payroll` | GET | Payroll generation |
| `/admin/marketing-campaigns` | GET, POST | Campaigns |
| `/admin/reports` | GET | Reports hub |
| `/admin/cache` | GET | Cache management |
| `/admin/system-health` | GET | Health monitoring |
| `/admin/audit-log` | GET | Audit log |
| `/admin/webhooks` | GET | Webhook config |
| `/admin/bulk-operations` | GET, POST | Bulk import/export |
| `/admin/api-keys` | GET, POST | API key CRUD |

---

## REST API

All REST endpoints return **JSON**. Set `Accept: application/json` for best results.

### Authentication

```
Authorization: Bearer <api_key>:<api_secret>
```

Create an API key + secret at `/admin/api-keys`. The secret is shown **once** on creation — copy it immediately.

### Standard Response Format

**Success**:
```json
{
    "success": true,
    "data": { ... }
}
```

**Error**:
```json
{
    "success": false,
    "error": "Error message",
    "code": "ERR_VALIDATION"
}
```

### Property Endpoints

#### GET `/api/properties`

List properties with filtering.

**Query parameters**:
- `type` – flat, villa, plot, farmhouse
- `listing_type` – buy, rent
- `city`, `district`, `state`
- `min_price`, `max_price`
- `bedrooms`, `bathrooms`
- `sort` – newest, price_asc, price_desc, popular
- `page`, `per_page` (default 20, max 100)

**Response**:
```json
{
    "success": true,
    "data": {
        "properties": [ { "id": 1, "title": "...", "price": 4500000, ... } ],
        "pagination": { "page": 1, "per_page": 20, "total": 156, "pages": 8 }
    }
}
```

#### GET `/api/properties/{id}`

Single property detail.

#### POST `/api/properties`

Create a property (auth required, `write:properties` scope).

### Lead Endpoints

#### GET `/api/leads`
#### POST `/api/leads`
#### GET `/api/leads/{id}`
#### PUT `/api/leads/{id}`
#### DELETE `/api/leads/{id}`

Lead CRUD with scope-checked auth.

### Booking Endpoints

#### GET `/api/bookings`
#### POST `/api/bookings`
#### GET `/api/bookings/{id}`

### Saved Search Endpoints

#### GET `/api/saved-searches/autocomplete?q=...`

Typeahead suggestions for property names, addresses, locations.

**Response**:
```json
{
    "success": true,
    "data": [
        { "type": "property", "label": "Suryoday Heights", "url": "/properties?colony=2" },
        { "type": "address",  "label": "Gorakhpur, UP",    "url": "/properties?city=gorakhpur" }
    ]
}
```

### Location Endpoints

#### GET `/api/locations/countries`
#### GET `/api/locations/states?country_id=X`
#### GET `/api/locations/districts?state_id=X`
#### GET `/api/locations/cities?district_id=X`
#### GET `/api/locations/pincode/{pincode}`
#### GET `/api/locations/search?q=...`

Cascading dropdowns for forms. No auth required.

### Bank Endpoints

#### GET `/api/banks/search?q=...`
#### GET `/api/banks/ifsc/{ifsc}`

IFSC lookup auto-fills bank name + branch + address.

### Analytics Endpoints

#### GET `/api/v2/analytics/dashboard` (admin)
#### GET `/api/v2/analytics/insights` (admin)

Real-time dashboard data + AI-generated insights.

### Notification Endpoints

#### POST `/api/notification` (admin)

Publish a notification to all or a specific user.

**Body**:
```json
{
    "user_id": 123,
    "event_type": "lead_created",
    "title": "New Lead",
    "message": "John Doe submitted inquiry",
    "url": "/admin/leads/456"
}
```

#### POST `/api/notifications/stream/poll`

Poll endpoint for the notification widget (called every 15s).

#### POST `/api/notifications/stream/mark-read`

Mark notification(s) as read.

### Referral Endpoints

#### GET `/api/referral/dashboard` (customer)

Returns the user's referral code, downline count, earnings.

### AI Endpoints

#### POST `/api/ai/chatbot`

Send a message to the AI chatbot.

**Body**:
```json
{
    "message": "I want a 3 BHK in Gorakhpur",
    "language": "en"
}
```

**Response**:
```json
{
    "intent": "search_property",
    "reply": "I found 5 matching properties...",
    "suggestions": [ ... ],
    "lead_created": true,
    "lead_id": 789
}
```

---

## Mobile API

The mobile JWT API lives under `/api/mobile/*`. It uses **JSON Web Tokens** for authentication.

### Login Flow

#### POST `/api/mobile/auth/login`

**Body**:
```json
{
    "identity": "user@example.com",
    "password": "secret123",
    "device_id": "abc-123",
    "device_name": "iPhone 14 Pro"
}
```

**Response**:
```json
{
    "success": true,
    "data": {
        "user": { "id": 1, "name": "John", "email": "..." },
        "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6...",
        "expires_at": "2026-06-12T12:00:00Z"
    }
}
```

### Authorized Requests

Include the token in every subsequent request:

```
Authorization: Bearer eyJhbGciOiJIUzI1NiIs...
```

### Mobile Endpoints

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/api/mobile/auth/login` | POST | Get JWT |
| `/api/mobile/auth/refresh` | POST | Refresh JWT before expiry |
| `/api/mobile/auth/logout` | POST | Invalidate JWT |
| `/api/mobile/profile` | GET, PUT | User profile |
| `/api/mobile/properties` | GET | Properties (same params as web API) |
| `/api/mobile/properties/{id}` | GET | Detail |
| `/api/mobile/properties/{id}/favorite` | POST, DELETE | Save/unsave |
| `/api/mobile/favorites` | GET | List favorites |
| `/api/mobile/saved-searches` | GET, POST, DELETE | Saved searches |
| `/api/mobile/bookings` | GET, POST | Bookings |
| `/api/mobile/visits/book` | POST | Schedule visit |
| `/api/mobile/notifications` | GET | List notifications |
| `/api/mobile/notifications/mark-read` | POST | Bulk mark-read |
| `/api/mobile/push/register` | POST | Register FCM/APNS token |
| `/api/mobile/upload` | POST | Upload image (multipart) |

### Push Notifications (Mobile)

Register the device's FCM (Android) or APNS (iOS) token:

```
POST /api/mobile/push/register
Body: { "token": "fcm_token_string", "platform": "android" }
```

We send pushes via the standard channels when relevant events occur.

---

## WebSocket Protocol

The WebSocket server runs on `ws://localhost:8080` (dev) or `wss://apsdreamhome.com/ws` (prod, via nginx upgrade).

### Connection Flow

1. **Client opens connection**.
2. **Server sends a "connection" frame**:
   ```json
   { "type": "connection", "status": "connected" }
   ```
3. **Client authenticates** (optional but recommended):
   ```json
   { "type": "auth", "token": "<jwt>", "userId": 1, "userRole": "admin" }
   ```
4. **Server confirms**:
   ```json
   { "type": "auth", "status": "success", "user_id": 1, "role": "admin" }
   ```

### Heartbeat

Client sends a ping every 30s to keep the connection alive:

```json
{ "type": "ping", "timestamp": 1717612800 }
```

Server responds:

```json
{ "type": "pong", "timestamp": 1717612800 }
```

### Receiving Notifications

When a notification is broadcast, the server pushes:

```json
{
    "type": "notification",
    "data": {
        "id": 123,
        "channel_name": "global",
        "user_id": null,
        "event_type": "lead_created",
        "payload": { "title": "New Lead", "message": "..." },
        "created_at": "2026-06-05 14:30:00"
    }
}
```

### Reconnection Strategy

The bundled client (`assets/js/notification-system.js`) implements **exponential backoff**:
- Initial delay: 1s
- Each retry: 2× last (1s, 2s, 4s, 8s, 16s)
- Capped at 30s
- Max 10 attempts before giving up

---

## Webhook Signature Verification

Every webhook payload we send is signed with HMAC-SHA256.

### Headers Sent

| Header | Value |
|--------|-------|
| `Content-Type` | `application/json` |
| `X-Webhook-Event` | `lead.created`, `booking.paid`, etc. |
| `X-Webhook-Signature` | `sha256=<hex digest>` |
| `X-Webhook-Timestamp` | Unix timestamp |
| `X-Webhook-Delivery-Id` | UUID for idempotency |
| `User-Agent` | `APSDreamHome-Webhook/1.0` |

### Verification (Node.js example)

```js
const crypto = require('crypto');

function verifyWebhook(rawBody, signatureHeader, secret) {
    const expected = 'sha256=' + crypto
        .createHmac('sha256', secret)
        .update(rawBody)
        .digest('hex');
    return crypto.timingSafeEqual(
        Buffer.from(expected),
        Buffer.from(signatureHeader)
    );
}
```

### Verification (PHP example)

```php
$rawBody = file_get_contents('php://input');
$sigHeader = $_SERVER['HTTP_X_WEBHOOK_SIGNATURE'];
$expected = 'sha256=' . hash_hmac('sha256', $rawBody, $secret);

if (hash_equals($expected, $sigHeader)) {
    // valid
}
```

### Retry Policy

- Failed deliveries (non-2xx response or timeout) are retried up to **3 times**.
- Backoff: 1 min → 5 min → 30 min.
- After 3 failures the webhook is marked as **`failed`** and visible in `/admin/webhooks → Deliveries`.

### Supported Events

| Event | Triggered When |
|-------|----------------|
| `lead.created` | New inquiry/lead submitted |
| `lead.updated` | Lead status / stage changes |
| `property.created` | New property submitted |
| `property.approved` | Admin approves property |
| `property.rejected` | Admin rejects property |
| `booking.created` | New booking placed |
| `booking.confirmed` | Admin confirms booking |
| `booking.cancelled` | Booking cancelled |
| `booking.paid` | Payment received |
| `user.registered` | New user signed up |
| `user.verified` | Email/phone verified |
| `commission.earned` | Associate earned commission |
| `commission.paid` | Payout disbursed |
| `*` | All events (wildcard) |

---

## Rate Limits

| Audience | Limit |
|----------|-------|
| Anonymous (per IP) | 30 req/sec |
| Authenticated web user | 60 req/sec |
| API key (default) | 60 req/min |
| API key (admin scope) | 600 req/min |
| Login endpoint | 5 req / 15 min per IP |
| Password reset | 3 req / hour per email |

### Rate-Limit Response Headers

Every response includes:

```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 47
X-RateLimit-Reset: 1717613060
```

When you exceed the limit:

```
HTTP/1.1 429 Too Many Requests
Retry-After: 30
```

```json
{
    "success": false,
    "error": "Rate limit exceeded. Retry after 30 seconds.",
    "code": "ERR_RATE_LIMIT"
}
```

---

## Standard Error Codes

| HTTP | Code | Meaning |
|------|------|---------|
| 200 | – | OK |
| 201 | – | Created |
| 204 | – | No Content |
| 301 / 302 | – | Redirect |
| 400 | `ERR_VALIDATION` | Invalid request body |
| 401 | `ERR_UNAUTHENTICATED` | No / invalid auth credentials |
| 403 | `ERR_FORBIDDEN` | Auth OK but no permission |
| 404 | `ERR_NOT_FOUND` | Resource doesn't exist |
| 409 | `ERR_CONFLICT` | Duplicate / state conflict |
| 422 | `ERR_VALIDATION` | Request body failed validation |
| 429 | `ERR_RATE_LIMIT` | Too many requests |
| 500 | `ERR_SERVER` | Unexpected server error |
| 503 | `ERR_SERVICE_UNAVAILABLE` | Maintenance or dependency down |

### Custom Error Codes (in API responses)

| Code | Meaning |
|------|---------|
| `ERR_VALIDATION` | Body / query validation failed |
| `ERR_UNAUTHENTICATED` | Missing or invalid token / session |
| `ERR_FORBIDDEN` | Scope insufficient |
| `ERR_NOT_FOUND` | Entity ID not in DB |
| `ERR_CONFLICT` | Duplicate creation attempt |
| `ERR_RATE_LIMIT` | Rate limit hit |
| `ERR_SERVER` | Internal exception |
| `ERR_DB` | Database error |
| `ERR_PAYMENT_FAILED` | Razorpay/UPI error |
| `ERR_2FA_REQUIRED` | Must provide 2FA code |
| `ERR_2FA_INVALID` | Wrong 2FA code |
| `ERR_TOKEN_EXPIRED` | JWT past `exp` |
| `ERR_CSRF` | CSRF token missing/invalid |

---

## Pagination

All list endpoints support pagination:

```
GET /api/properties?page=2&per_page=50
```

### Response shape

```json
{
    "success": true,
    "data": {
        "items": [ ... ],
        "pagination": {
            "page": 2,
            "per_page": 50,
            "total": 287,
            "pages": 6,
            "next_page": 3,
            "prev_page": 1
        }
    }
}
```

### Limits

- `per_page` default: 20.
- `per_page` max: 100.
- `page` is 1-indexed.

---

## Date / Time Formats

- **Date**: `YYYY-MM-DD` (e.g., `2026-06-05`)
- **Datetime**: `YYYY-MM-DD HH:MM:SS` (server local) or ISO-8601 with TZ for APIs (`2026-06-05T14:30:00+05:30`)
- **Timestamp**: Unix epoch in seconds (e.g., `1717612800`).
- **Timezone**: India Standard Time (IST = UTC+5:30) by default.

---

**Last Updated:** June 5, 2026
**Document Version:** 1.0
**See also:** [User Guide](USER_GUIDE.md) · [Admin Manual](ADMIN_MANUAL.md) · [Developer Guide](DEVELOPER_GUIDE.md)

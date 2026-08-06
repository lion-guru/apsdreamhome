# PART 6: API ARCHITECTURE

## 24. REST API Design

### API Design Principles

```
1. RESTful URLs:    /resources/{id}/sub-resources
2. HTTP Methods:    GET (read), POST (create), PUT (update), DELETE (remove)
3. Status Codes:    200 (OK), 201 (Created), 400 (Bad Request), 401 (Unauthorized)
4. Response Format: JSON with success/error envelope
5. Authentication:  JWT Bearer Token (API), Session Cookie (Web)
6. Versioning:      /api/v2/mobile/ (Mobile API v2)
```

### Standard Response Format

**Success:**
```json
{
  "success": true,
  "data": { ... },
  "message": "Operation successful"
}
```

**Error:**
```json
{
  "success": false,
  "error": "Human readable message",
  "code": "ERROR_CODE",
  "details": { ... }
}
```

## 25. API Endpoint Catalog

### Authentication Endpoints

| Method | Endpoint | Purpose | Auth |
|--------|----------|---------|------|
| POST | `/auth/login` | Customer login | Public |
| POST | `/auth/air-login` | Request OTP | Public |
| POST | `/auth/air-login/verify` | Verify OTP | Public |
| POST | `/auth/register` | Registration | Public |
| POST | `/auth/forgot-password` | Password reset | Public |
| GET | `/auth/me` | Current user | Bearer |
| POST | `/auth/logout` | Logout | Bearer |

### Mobile API v2 Endpoints

| Method | Endpoint | Purpose | Auth |
|--------|----------|---------|------|
| GET | `/api/v2/mobile/properties` | Property listings | Bearer |
| GET | `/api/v2/mobile/properties/{id}` | Property detail | Bearer |
| POST | `/api/v2/mobile/bookings` | Create booking | Bearer |
| GET | `/api/v2/mobile/bookings` | My bookings | Bearer |
| GET | `/api/v2/mobile/bookings/{id}` | Booking detail | Bearer |
| POST | `/api/v2/mobile/leads` | Create lead | Bearer |
| GET | `/api/v2/mobile/leads` | My leads | Bearer |
| GET | `/api/v2/mobile/mlm/tree` | Network tree | Bearer |
| GET | `/api/v2/mobile/mlm/commissions` | Commission data | Bearer |
| POST | `/api/v2/mobile/notifications/read` | Mark read | Bearer |
| POST | `/api/ai/chat` | AI chatbot | Public |

### Admin API Endpoints

| Method | Endpoint | Purpose | Auth |
|--------|----------|---------|------|
| GET | `/api/admin/dashboard` | Dashboard stats | Session |
| GET | `/api/admin/sales-trend` | Sales analytics | Session |
| GET | `/api/admin/lead-conversion` | Conversion rate | Session |
| GET | `/api/admin/emi-collection` | EMI tracking | Session |

## 26. Authentication and Authorization

### Multi-Layer Auth

```
Layer 1: Route-level     → CSRF check (web), JWT check (api)
Layer 2: Controller-level → requireAdmin(), requireLogin()
Layer 3: Service-level   → tenant_id filtering
Layer 4: Model-level     → Model::$tenantScoped = true
```

### Token Flow

```
┌──────────┐     ┌──────────┐     ┌──────────┐
│  Client  │────▶│  Login   │────▶│  Token   │
│          │     │  Verify  │     │  Issue   │
└──────────┘     └──────────┘     └──────────┘
     │                                 │
     │                                 ▼
     │                          ┌──────────┐
     │                          │  Store   │
     │                          │  Token   │
     │                          └──────────┘
     │                                 │
     ▼                                 ▼
┌──────────┐     ┌──────────┐     ┌──────────┐
│ Request  │────▶│  Verify  │────▶│  Grant   │
│ + Token  │     │  Token   │     │  Access  │
└──────────┘     └──────────┘     └──────────┘
```

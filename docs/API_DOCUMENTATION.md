# API Documentation

> **Base URL:** `http://localhost/apsdreamhome`  
> **API Version:** v2  
> **Authentication:** JWT Bearer Token (mobile) / Session Cookie (web)  
> **Last Updated:** 2026-08-06  

---

## Authentication

### Login
```
POST /api/v2/mobile/auth/login
Content-Type: application/json

{
    "email": "user@example.com",
    "password": "password123"
}

Response 200:
{
    "success": true,
    "token": "eyJhbGciOiJIUzI1NiIs...",
    "user": { "id": 1, "name": "John", "role": "customer" }
}
```

### Register
```
POST /api/v2/mobile/auth/register
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "9876543210",
    "password": "password123",
    "referral_code": "ABC123"
}

Response 201:
{
    "success": true,
    "message": "Registration successful",
    "user_id": 123
}
```

### Air Login (OTP)
```
POST /auth/air-login
Content-Type: application/json

{ "identifier": "user@example.com" }

Response 200:
{ "success": true, "message": "OTP sent" }

POST /auth/air-login/verify
Content-Type: application/json

{ "otp": "123456" }

Response 200:
{ "success": true, "redirect": "/user/dashboard" }
```

### Logout
```
POST /api/v2/mobile/auth/logout
Authorization: Bearer {token}

Response 200:
{ "success": true, "message": "Logged out" }
```

---

## Properties

### List Properties
```
GET /api/v2/mobile/properties
Authorization: Bearer {token}

Response 200:
{
    "success": true,
    "data": [
        {
            "id": 1,
            "title": "Plot in Suryoday Colony",
            "price": 1500000,
            "area": 1000,
            "colony_name": "Suryoday Colony",
            "status": "available"
        }
    ]
}
```

### Property Detail
```
GET /api/v2/mobile/properties/{id}
Authorization: Bearer {token}

Response 200:
{
    "success": true,
    "data": { ... }
}
```

### Submit Property
```
POST /api/v2/mobile/properties/submit
Authorization: Bearer {token}
Content-Type: application/json

{
    "title": "My Property",
    "description": "Description",
    "price": 1000000,
    "area": 1000,
    "address": "Address",
    "city": "Gorakhpur"
}

Response 201:
{ "success": true, "property_id": 123 }
```

---

## Bookings

### My Bookings
```
GET /api/v2/mobile/customer/bookings
Authorization: Bearer {token}

Response 200:
{
    "success": true,
    "data": [
        {
            "id": 1,
            "booking_number": "APS-BK-20260806-0001",
            "plot_number": "ST-A-001",
            "total_amount": 1500000,
            "status": "token_paid"
        }
    ]
}
```

### EMI Schedule
```
GET /api/v2/mobile/customer/emi-schedule?booking_id=1
Authorization: Bearer {token}

Response 200:
{
    "success": true,
    "data": [
        {
            "installment_number": 1,
            "due_date": "2026-09-01",
            "amount": 50000,
            "status": "pending"
        }
    ]
}
```

### Pay EMI
```
POST /api/v2/mobile/customer/pay-emi
Authorization: Bearer {token}
Content-Type: application/json

{
    "booking_id": 1,
    "installment_id": 1,
    "amount": 50000,
    "payment_mode": "online"
}

Response 200:
{ "success": true, "message": "Payment recorded" }
```

---

## Leads

### My Leads
```
GET /api/v2/mobile/leads
Authorization: Bearer {token}

Response 200:
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Lead Name",
            "phone": "9876543210",
            "status": "new",
            "lead_score": 75
        }
    ]
}
```

### Create Lead
```
POST /api/v2/mobile/leads
Authorization: Bearer {token}
Content-Type: application/json

{
    "name": "Lead Name",
    "phone": "9876543210",
    "email": "lead@example.com",
    "source": "referral",
    "budget": 2000000,
    "notes": "Interested in plot"
}

Response 201:
{ "success": true, "lead_id": 123 }
```

### Batch Sync Leads
```
POST /api/v2/mobile/leads/batch-sync
Authorization: Bearer {token}
Content-Type: application/json

{
    "leads": [
        { "name": "Lead 1", "phone": "9876543210" },
        { "name": "Lead 2", "phone": "9876543211" }
    ]
}

Response 200:
{ "success": true, "synced": 2 }
```

---

## MLM Commission

### MLM Summary
```
GET /api/v2/mobile/mlm/summary
Authorization: Bearer {token}

Response 200:
{
    "success": true,
    "data": {
        "total_commission": 50000,
        "pending_payout": 25000,
        "network_size": 15,
        "rank": "associate"
    }
}
```

### Payouts
```
GET /api/v2/mobile/mlm/payouts
Authorization: Bearer {token}

Response 200:
{
    "success": true,
    "data": [
        {
            "id": 1,
            "amount": 25000,
            "status": "pending",
            "created_at": "2026-08-01"
        }
    ]
}
```

### Request Payout
```
POST /api/v2/mobile/mlm/request-payout
Authorization: Bearer {token}
Content-Type: application/json

{ "amount": 25000 }

Response 200:
{ "success": true, "message": "Payout requested" }
```

### Genealogy
```
GET /api/v2/mobile/mlm/genealogy
Authorization: Bearer {token}

Response 200:
{
    "success": true,
    "data": {
        "user": { "id": 1, "name": "John" },
        "children": [
            { "id": 2, "name": "Referral 1", "children": [] }
        ]
    }
}
```

---

## Notifications

### Get Notifications
```
GET /api/v2/mobile/user/notifications
Authorization: Bearer {token}

Response 200:
{
    "success": true,
    "data": [
        {
            "id": 1,
            "title": "Payment Received",
            "message": "Your payment of ₹50000 received",
            "is_read": false,
            "created_at": "2026-08-06"
        }
    ]
}
```

### Mark as Read
```
POST /api/v2/mobile/user/notifications/read
Authorization: Bearer {token}
Content-Type: application/json

{ "notification_id": 1 }

Response 200:
{ "success": true }
```

---

## User Profile

### Get Profile
```
GET /api/v2/mobile/user/profile
Authorization: Bearer {token}

Response 200:
{
    "success": true,
    "data": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "phone": "9876543210",
        "role": "customer"
    }
}
```

### Update Profile
```
PUT /api/v2/mobile/user/profile
Authorization: Bearer {token}
Content-Type: application/json

{
    "name": "John Doe Updated",
    "phone": "9876543210"
}

Response 200:
{ "success": true, "message": "Profile updated" }
```

---

## Error Codes

| Code | HTTP | Description |
|------|------|-------------|
| 200 | OK | Success |
| 201 | Created | Resource created |
| 400 | Bad Request | Invalid input |
| 401 | Unauthorized | Authentication required |
| 403 | Forbidden | Permission denied |
| 404 | Not Found | Resource not found |
| 429 | Too Many Requests | Rate limit exceeded |
| 500 | Server Error | Internal error |

---

## Rate Limits

| Endpoint Type | Limit | Window |
|---------------|-------|--------|
| Auth | 10 requests | per minute |
| API | 120 requests | per minute |
| Web | 600 requests | per minute |

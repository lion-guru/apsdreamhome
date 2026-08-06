# 🔌 Unified API Master Documentation & Technical Specifications
> **Document Type:** Complete Unified REST API Documentation (Web & Mobile API Endpoints)  
> **Platform:** APS Dream Home (Real Estate ERP, CRM, MLM & Multi-Tenant White-Label SaaS)  
> **Prepared By:** Senior Lead Software Developer & Chief Architect  

---

## 📌 1. API Architecture & Authentication

All API endpoints follow RESTful standards and return JSON payloads.

```
Header Format:
Authorization: Bearer <JWT_TOKEN>
X-Tenant-ID: <TENANT_ID>
Content-Type: application/json
```

---

## 📑 2. API Endpoint Matrix

### 🔐 1. Authentication & User Profile APIs
- `POST /api/v1/auth/login` — User login & JWT token generation.
- `POST /api/v1/auth/register` — Associate / Customer registration.
- `GET /api/v1/user/profile` — Fetch current user profile.
- `POST /api/v1/user/update-profile` — Update user profile details.

### 🗺️ 2. Property & Plot Layout APIs
- `GET /api/v1/colonies` — List all active real estate colonies.
- `GET /api/v1/plots?colony_id={id}` — Fetch plot inventory for a colony.
- `GET /api/v1/plots/layout/{colony_id}` — Interactive SVG layout map data.
- `GET /api/v1/plots/details/{plot_id}` — Detailed plot costing & status.

### 💳 3. Bookings, EMI & Receipt APIs
- `POST /api/v1/bookings/create` — Process new plot booking & token payment.
- `GET /api/v1/bookings/my-bookings` — Customer booking history.
- `GET /api/v1/installments/schedule/{booking_id}` — Customer EMI Khatabook schedule.
- `GET /api/v1/payments/receipt/{receipt_id}` — Fetch digital PDF payment receipt.

### 🌳 4. MLM Associate Network & Payout APIs
- `GET /api/v1/mlm/tree` — Visual binary genealogy tree.
- `GET /api/v1/mlm/commissions` — Associate earned commissions ledger.
- `GET /api/v1/mlm/wallet-balance` — Associate E-Wallet balance.
- `POST /api/v1/mlm/request-payout` — Submit payout withdrawal request.

### 🎯 5. CRM, Leads & Telephony APIs
- `POST /api/v1/leads/create` — Meta/Google Ads lead capture webhook.
- `GET /api/v1/leads/kanban` — CRM Kanban funnel stages.
- `POST /api/v1/telephony/click-to-call` — Trigger auto-dialer call to lead.
- `POST /api/v1/site-visits/check-in` — GPS Geo-fenced site visit check-in.

---

## 🔒 3. API Response Payload Format

### Success Response (200 OK):
```json
{
  "success": true,
  "code": 200,
  "message": "Data retrieved successfully",
  "data": {
    "booking_id": 1048,
    "plot_number": "A-12",
    "status": "Booked",
    "total_amount": 1500000.00
  }
}
```

### Error Response (400 / 401 / 500):
```json
{
  "success": false,
  "code": 401,
  "message": "Unauthorized: Invalid or expired JWT token",
  "errors": []
}
```

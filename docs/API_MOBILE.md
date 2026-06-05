# Mobile API V2 — JWT Authenticated Endpoints

REST API for APS Dream Home mobile apps. All endpoints return JSON. Auth uses
**JWT (HS256)** issued by `/api/mobile/auth/login`. The token is sent on every
subsequent request via the `Authorization: Bearer <token>` header.

Base URL: `http://localhost/apsdreamhome` (override in production via
`BASE_URL`).

---

## Endpoints

| # | Method | Path                                          | Auth   | Description                       |
|---|--------|-----------------------------------------------|--------|-----------------------------------|
| 1 | POST   | `/api/mobile/auth/login`                      | none   | Exchange email+password for JWT   |
| 2 | POST   | `/api/mobile/auth/refresh`                    | none   | Refresh an access token           |
| 3 | GET    | `/api/mobile/profile`                         | Bearer | Get the logged-in user's profile  |
| 4 | GET    | `/api/mobile/properties`                      | Bearer | List the user's properties (20/pg)|
| 5 | GET    | `/api/mobile/dashboard`                       | Bearer | Aggregated stats                  |
| 6 | POST   | `/api/mobile/notifications/register`          | Bearer | Register a push notification token|

---

## Auth flow

1. **Login** — POST email + password, receive `access_token` + `refresh_token`.
2. **Use** — send `Authorization: Bearer <access_token>` on every request.
3. **Refresh** — when the access token expires, POST the `refresh_token` to
   `/api/mobile/auth/refresh` to get a new pair.
4. **Rate limit** — 60 requests per minute per user, sliding window.

### Login

```bash
curl -X POST http://localhost/apsdreamhome/api/mobile/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"customer1@apsdreamhome.com","password":"Test1234"}'
```

Response (200):
```json
{
  "success": true,
  "access_token": "eyJ0eXAiOiJKV1Q...",
  "refresh_token": "ab12cd34...",
  "token_type": "Bearer",
  "expires_in": 86400,
  "user_id": 3,
  "role": "customer",
  "name": "Customer One",
  "email": "customer1@apsdreamhome.com"
}
```

Error (401):
```json
{ "success": false, "error": "Invalid credentials", "code": 401 }
```

### Profile

```bash
curl -H "Authorization: Bearer <access_token>" \
  http://localhost/apsdreamhome/api/mobile/profile
```

Response (200):
```json
{
  "success": true,
  "data": {
    "id": 3,
    "name": "Customer One",
    "email": "customer1@apsdreamhome.com",
    "phone": "9876543210",
    "role": "customer",
    "status": "active",
    "wallet_balance": "0.00",
    "mlm_points": 0
  }
}
```

### Properties (paginated, 20 per page)

```bash
curl -H "Authorization: Bearer <access_token>" \
  "http://localhost/apsdreamhome/api/mobile/properties?page=1"
```

Response (200):
```json
{
  "success": true,
  "data": {
    "properties": [ /* ... */ ],
    "pagination": {
      "current_page": 1,
      "per_page": 20,
      "total": 12,
      "total_pages": 1
    }
  }
}
```

### Dashboard

```bash
curl -H "Authorization: Bearer <access_token>" \
  http://localhost/apsdreamhome/api/mobile/dashboard
```

Response (200):
```json
{
  "success": true,
  "data": {
    "property_count": 3,
    "lead_count": 0,
    "unread_notifications": 0,
    "wallet_balance": 0,
    "mlm_points": 0
  }
}
```

### Refresh

```bash
curl -X POST http://localhost/apsdreamhome/api/mobile/auth/refresh \
  -H "Content-Type: application/json" \
  -d '{"refresh_token":"<refresh_token>"}'
```

Response (200):
```json
{
  "success": true,
  "access_token": "eyJ...",
  "refresh_token": "...",
  "token_type": "Bearer",
  "expires_in": 86400
}
```

### Register push token

```bash
curl -X POST http://localhost/apsdreamhome/api/mobile/notifications/register \
  -H "Authorization: Bearer <access_token>" \
  -H "Content-Type: application/json" \
  -d '{"device_token":"fcm_token_xxx","platform":"android","device_id":"ab-12"}'
```

Response (200):
```json
{ "success": true, "message": "Push token registered" }
```

---

## Rate limit

| Setting           | Value          |
|-------------------|----------------|
| Limit             | 60 requests    |
| Window            | 60 seconds     |
| Key               | `mobile_user_<id>` |
| Storage           | `rate_limits` table |

When the limit is exceeded the API responds with HTTP 429:

```json
{
  "success": false,
  "error": "Rate limit exceeded. Max 60 requests per minute.",
  "code": 429
}
```

---

## Error codes

| HTTP | Meaning                                  |
|------|------------------------------------------|
| 200  | OK                                       |
| 400  | Bad request (missing required fields)    |
| 401  | Unauthorized (missing/invalid token, bad credentials) |
| 403  | Forbidden (suspended account)            |
| 404  | Not found                                |
| 429  | Too many requests (rate limit)           |
| 500  | Internal server error                    |

---

## Configuration

| Env var       | Purpose                            | Default                           |
|---------------|------------------------------------|-----------------------------------|
| `JWT_SECRET`  | HMAC-SHA256 secret                 | auto-generated 64-char base64     |

If `JWT_SECRET` is not set, a per-process random secret is generated. **Set
this in production** so tokens survive PHP worker restarts.

---

## Files

| Path                                                       | Role                                   |
|------------------------------------------------------------|----------------------------------------|
| `app/Services/Auth/JWTAuthService.php`                     | JWT issue/verify/refresh, rate limit   |
| `app/Http/Controllers/Api/MobileApiController.php`         | 6 V2 endpoints                         |
| `routes/api.php`                                           | Route registrations                    |
| `scripts/add_rate_limits_table.php`                        | Creates `rate_limits`, `api_tokens`, `push_tokens` |
| `testing/test_mobile_api.php`                              | 6-case test suite                      |

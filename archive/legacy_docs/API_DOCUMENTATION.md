# APS Dream Home API Documentation

## Base URL
`https://apsdreamhome.com/api/v1`

## Authentication
Most endpoints require a JWT token or an active session.
Header: `Authorization: Bearer <your_token>`

---

## 1. Push Notifications

### Register Device
Registers a device for push notifications.
- **URL:** `/notifications/register`
- **Method:** `POST`
- **Auth Required:** Yes
- **Payload:**
```json
{
  "token": "push_token_string",
  "platform": "web|android|ios"
}
```

### Unregister Device
- **URL:** `/notifications/unregister`
- **Method:** `POST`
- **Payload:**
```json
{
  "token": "push_token_string"
}
```

### Test Notification (Admin Only)
- **URL:** `/notifications/test`
- **Method:** `POST`
- **Payload:**
```json
{
  "user_id": 123,
  "title": "Test Title",
  "message": "Test Message"
}
```

---

## 2. Referrals & Sharing

### Create Referral Link
- **URL:** `/referrals`
- **Method:** `POST`
- **Auth Required:** Yes
- **Payload:**
```json
{
  "email": "friend@example.com"
}
```

### List Referrals (Admin/Agent)
- **URL:** `/referrals`
- **Method:** `GET`
- **Auth Required:** Yes
- **Response:** List of referrals created by the user (or all for admin).

### Get Referral Stats
- **URL:** `/referrals/stats`
- **Method:** `GET`
- **Auth Required:** Yes (Agent)
- **Response:**
```json
{
  "success": true,
  "data": {
    "total": 150,
    "converted": 10,
    "pending": 140
  }
}
```

### Generate Share Link
- **URL:** `/sharing/generate`
- **Method:** `GET`
- **Params:** `type` (e.g., 'property'), `id`
- **Response:** Tracking URL for social sharing.

### Track Click
- **URL:** `/sharing/track`
- **Method:** `POST`
- **Auth Required:** No
- **Payload:**
```json
{
  "source": "facebook",
  "medium": "social",
  "campaign": "summer_sale",
  "landing_page": "/property-detail.php?id=123"
}
```

---

## 3. Properties (Cached)

### List Properties
- **URL:** `/properties`
- **Method:** `GET`
- **Params:** `status`, `min_price`, `max_price`, `bedrooms`, `type`
- **Caching:** 30 minutes TTL.

### Get Property Locations
Returns distinct locations (city/state) where properties are available.
- **URL:** `/properties/locations`
- **Method:** `GET`
- **Auth Required:** No
- **Response:**
```json
{
  "success": true,
  "data": [
    {
      "city": "Lucknow",
      "state": "Uttar Pradesh",
      "count": 45
    }
  ]
}
```

### Get Property Details
- **URL:** `/properties/{id}`
- **Method:** `GET`
- **Caching:** 60 minutes TTL.

### Create Property (Admin/Agent)
- **URL:** `/properties`
- **Method:** `POST`
- **Auth Required:** Yes
- **Payload:** Property object (title, price, type, location, etc.)

### Update Property (Admin/Agent)
- **URL:** `/properties/{id}`
- **Method:** `PUT`
- **Auth Required:** Yes

### Delete Property (Admin/Agent)
- **URL:** `/properties/{id}`
- **Method:** `DELETE`
- **Auth Required:** Yes

---

## 4. Leads & Enquiries

### Submit Enquiry
Capture interest in a property or project.
- **URL:** `/leads`
- **Method:** `POST`
- **Auth Required:** No
- **Payload:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "9876543210",
  "message": "I am interested in this property.",
  "property_id": 123,
  "project_code": "PROJ001",
  "utm_source": "facebook",
  "utm_medium": "ad",
  "utm_campaign": "summer_sale"
}
```
- **Side Effect:** Automatically notifies the assigned agent or admin via push notification.

### List Leads (Admin Only)
- **URL:** `/leads`
- **Method:** `GET`
- **Auth Required:** Yes (Admin/Agent)
- **Response:** Paginated list of enquiries.

---

## 5. Authentication

### Login
- **URL:** `/auth/login`
- **Method:** `POST`
- **Payload:**
```json
{
  "email": "user@example.com",
  "password": "yourpassword"
}
```
- **Response:** Returns JWT token and user info.

### Token Refresh
- **URL:** `/auth/refresh`
- **Method:** `POST`
- **Auth Required:** No (uses refresh token in cookie/payload)

### Get Current User (Me)
- **URL:** `/auth/me`
- **Method:** `GET`
- **Auth Required:** Yes

### Logout
- **URL:** `/auth/logout`
- **Method:** `POST`
- **Auth Required:** Yes

---

## 6. Users & Dashboard

### List Users (Admin Only)
- **URL:** `/users`
- **Method:** `GET`
- **Auth Required:** Yes (Admin)
- **Params:** `role`, `status`, `page`, `per_page`

### Create User (Admin Only)
- **URL:** `/users`
- **Method:** `POST`
- **Auth Required:** Yes (Admin)
- **Payload:** User object (name, email, password, role)

### Get User Details (Admin Only)
- **URL:** `/users/{id}`
- **Method:** `GET`
- **Auth Required:** Yes (Admin)

### Update User (Admin Only)
- **URL:** `/users/{id}`
- **Method:** `PUT`
- **Auth Required:** Yes (Admin)
- **Payload:** User data to update

### Delete User (Admin Only)
- **URL:** `/users/{id}`
- **Method:** `DELETE`
- **Auth Required:** Yes (Admin)

### Get User Profile
- **URL:** `/profile`
- **Method:** `GET`
- **Auth Required:** Yes

### Update User Profile
- **URL:** `/profile`
- **Method:** `PUT`
- **Auth Required:** Yes
- **Payload:** Profile data (name, email, phone, bio, etc.)

---

## 7. SEO & Metadata

### Get SEO Metadata
- **URL:** `/seo/metadata`
- **Method:** `GET`
- **Params:** `url` (e.g., `/` or `/property-detail.php?id=123`)
- **Response:** Returns title, description, and OG tags for the specified page.

---

## 8. Workflows (Admin Only)

### List Workflows
- **URL:** `/workflows`
- **Method:** `GET`
- **Auth Required:** Yes (Admin)

### Create Workflow
- **URL:** `/workflows`
- **Method:** `POST`
- **Auth Required:** Yes (Admin)
- **Payload:** Workflow definition (name, steps, triggers)

### Update Workflow
- **URL:** `/workflows/{id}`
- **Method:** `PUT`
- **Auth Required:** Yes (Admin)
- **Payload:** Updated workflow definition

### Delete Workflow
- **URL:** `/workflows/{id}`
- **Method:** `DELETE`
- **Auth Required:** Yes (Admin)

---

## Performance & Caching
- **API Caching:** Implemented using file-based storage.
- **Cache Warming:** Automated script available at `/scripts/warm_cache.php`.
- **Rate Limiting:** Enforced per user/IP.

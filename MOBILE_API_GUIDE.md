# 📱 APS Dream Home - Mobile API Guide
**Complete API Reference for Flutter/Mobile Apps**

---

## 🎯 BASE URL
```
Production: https://apsdreamhome.com/api/
Development: http://localhost/apsdreamhome/api/
```

---

## 🔐 AUTHENTICATION

### JWT Token Flow
All protected endpoints require Bearer token in header:
```http
Authorization: Bearer <your-jwt-token>
```

### 1. Login
```http
POST /api/v2/mobile/auth/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "your_password",
  "device_info": "Flutter Android"
}
```

**Response:**
```json
{
  "success": true,
  "access_token": "eyJhbGciOiJIUzI1NiIs...",
  "refresh_token": "a1b2c3d4e5f6...",
  "token_type": "Bearer",
  "expires_in": 86400,
  "user": {
    "id": 1,
    "name": "Rahul Kumar",
    "email": "user@example.com",
    "phone": "+91 98765 43210",
    "type": "customer"
  }
}
```

### 2. Refresh Token
```http
POST /api/v1/auth/refresh
Content-Type: application/json

{
  "refresh_token": "a1b2c3d4e5f6..."
}
```

### 3. Register Push Token
```http
POST /api/v1/auth/push-token
Authorization: Bearer <token>
Content-Type: application/json

{
  "device_token": "fcm_token_here",
  "platform": "android",
  "device_id": "device_unique_id"
}
```

---

## 🏠 PROPERTIES API

### 1. Search Properties
```http
GET /api/v1/search/properties?type=plot&city=gorakhpur&min_price=500000&max_price=2000000&page=1
```

**Query Parameters:**
| Param | Type | Description |
|-------|------|-------------|
| query | string | Search text |
| type | string | plot, house, flat, shop |
| city | string | City name |
| min_price | number | Minimum price |
| max_price | number | Maximum price |
| min_area | number | Minimum area sqft |
| max_area | number | Maximum area sqft |
| bedrooms | number | Number of bedrooms |
| furnishing | string | furnished, semi-furnished, unfurnished |
| sort | string | price_low, price_high, newest, popular |
| page | number | Page number (default: 1) |

**Response:**
```json
{
  "success": true,
  "data": {
    "properties": [
      {
        "id": 1,
        "title": "3 BHK Villa in Surya City",
        "type": "house",
        "price": 1500000,
        "area": 1800,
        "location": "Surya City, Gorakhpur",
        "bedrooms": 3,
        "bathrooms": 2,
        "furnishing": "semi-furnished",
        "primary_image": "uploads/property_1.jpg",
        "avg_rating": 4.5,
        "review_count": 12
      }
    ],
    "total": 45,
    "per_page": 20,
    "current_page": 1,
    "last_page": 3,
    "facets": {
      "types": [{"type": "plot", "count": 20}],
      "price_ranges": {"under_5l": 5, "5l_to_10l": 15},
      "cities": [{"city": "Gorakhpur", "count": 30}]
    }
  }
}
```

### 2. Get Property Details
```http
GET /api/v2/mobile/properties?id=123
```

### 3. Get Search Suggestions
```http
GET /api/v1/search/suggestions?query=surya
```

**Response:**
```json
{
  "locations": [
    {"location": "Surya City", "city": "Gorakhpur"},
    {"location": "Surya Nagar", "city": "Lucknow"}
  ],
  "popular_searches": [
    {"query": "plots in gorakhpur", "count": 156},
    {"query": "3 bhk house", "count": 89}
  ]
}
```

### 4. Trending Searches
```http
GET /api/v1/search/trending
```

### 5. Save Search
```http
POST /api/v1/search/save
Authorization: Bearer <token>
Content-Type: application/json

{
  "name": "My Dream Plot",
  "criteria": {
    "type": "plot",
    "city": "Gorakhpur",
    "max_price": 1000000
  },
  "alert_frequency": "daily"
}
```

---

## ❤️ WISHLIST API

### 1. Get Wishlist
```http
GET /api/v1/user/wishlist
Authorization: Bearer <token>
```

### 2. Add to Wishlist
```http
POST /api/v1/user/wishlist/add
Authorization: Bearer <token>
Content-Type: application/json

{
  "property_id": 123,
  "notes": "Perfect location for family",
  "priority": "high"
}
```

### 3. Remove from Wishlist
```http
POST /api/v1/user/wishlist/remove
Authorization: Bearer <token>
Content-Type: application/json

{
  "property_id": 123
}
```

### 4. Recently Viewed
```http
GET /api/v1/user/recently-viewed
Authorization: Bearer <token>
```

### 5. Compare Properties
```http
POST /api/v1/user/compare
Content-Type: application/json

{
  "property_ids": [101, 102, 103]
}
```

---

## 💰 EMI & FINANCE API

### 1. Calculate EMI
```http
POST /api/v1/finance/emi-calculate
Content-Type: application/json

{
  "principal": 1500000,
  "interest_rate": 8.5,
  "years": 20
}
```

**Response:**
```json
{
  "principal": 1500000,
  "interest_rate": 8.5,
  "tenure_years": 20,
  "emi_amount": 12987.50,
  "total_interest": 1617000,
  "total_payment": 3117000,
  "schedule": [
    {"month": 1, "emi": 12987.50, "principal": 2375.00, "interest": 10612.50, "balance": 1497625.00}
  ]
}
```

### 2. Get Bank Interest Rates
```http
GET /api/v1/finance/bank-rates
```

### 3. Check Affordability
```http
POST /api/v1/finance/affordability
Content-Type: application/json

{
  "monthly_income": 50000,
  "existing_emis": 5000,
  "other_obligations": 3000
}
```

### 4. Prepayment Benefit
```http
POST /api/v1/finance/prepayment-benefit
Content-Type: application/json

{
  "outstanding_principal": 1200000,
  "annual_rate": 8.5,
  "remaining_years": 15,
  "prepayment_amount": 200000
}
```

---

## 📅 SITE VISIT API

### 1. Get Available Slots
```http
GET /api/v1/site-visit/available-slots?property_id=123&date=2026-05-15
```

**Response:**
```json
{
  "slots": [
    {"time": "09:00", "available": true},
    {"time": "10:00", "available": false},
    {"time": "11:00", "available": true}
  ]
}
```

### 2. Book Site Visit
```http
POST /api/v1/site-visit/book
Authorization: Bearer <token>
Content-Type: application/json

{
  "property_id": 123,
  "visit_date": "2026-05-15",
  "visit_time": "10:00",
  "visitor_name": "Rahul Kumar",
  "visitor_phone": "9876543210",
  "visit_type": "site_visit",
  "pickup_required": false,
  "notes": "Interested in 3 BHK"
}
```

### 3. My Visits
```http
GET /api/v1/site-visit/my-visits
Authorization: Bearer <token>
```

### 4. Cancel Visit
```http
POST /api/v1/site-visit/cancel
Authorization: Bearer <token>
Content-Type: application/json

{
  "visit_id": 456,
  "reason": "Schedule conflict"
}
```

---

## 🔔 PRICE ALERTS API

### 1. Create Price Alert
```http
POST /api/v1/alerts/price/create
Authorization: Bearer <token>
Content-Type: application/json

{
  "property_id": 123,
  "target_price": 1400000,
  "alert_type": "below"
}
```

### 2. My Price Alerts
```http
GET /api/v1/alerts/price/my-alerts
Authorization: Bearer <token>
```

### 3. Create Property Alert (New Listings)
```http
POST /api/v1/alerts/property/create
Authorization: Bearer <token>
Content-Type: application/json

{
  "alert_name": "Plots in Gorakhpur",
  "property_type": "plot",
  "city": "Gorakhpur",
  "max_price": 1000000,
  "min_area": 1000,
  "frequency": "daily"
}
```

---

## 👤 USER PROFILE API

### 1. Get Profile
```http
GET /api/v2/mobile/user/profile
Authorization: Bearer <token>
```

### 2. Update Profile
```http
POST /api/v2/mobile/user/profile
Authorization: Bearer <token>
Content-Type: application/json

{
  "name": "Rahul Kumar",
  "phone": "9876543210",
  "address": "123, Main Street"
}
```

### 3. My Bookings
```http
GET /api/v2/mobile/customer/bookings
Authorization: Bearer <token>
```

### 4. My EMI Schedule
```http
GET /api/v2/mobile/customer/emi-schedule
Authorization: Bearer <token>
```

---

## 🗺️ LOCATION API

### 1. Get All States
```http
GET /api/locations
```

### 2. Get Districts by State
```http
GET /api/locations/state/{state_id}
```

### 3. Get Areas by District
```http
GET /api/locations/district/{district_id}
```

---

## 📤 UPLOAD API

### Upload Document
```http
POST /api/v2/mobile/upload-document
Authorization: Bearer <token>
Content-Type: multipart/form-data

file: [binary file data]
type: "identity" | "address" | "income"
```

---

## ❌ ERROR RESPONSES

### 401 Unauthorized
```json
{
  "success": false,
  "error": "Unauthorized",
  "message": "Invalid or expired token"
}
```

### 422 Validation Error
```json
{
  "success": false,
  "error": "Validation Error",
  "errors": {
    "email": ["Email is required"],
    "password": ["Password must be at least 6 characters"]
  }
}
```

### 429 Rate Limit
```json
{
  "success": false,
  "error": "Too Many Requests",
  "retry_after": 60
}
```

---

## 📊 API ENDPOINTS SUMMARY

| Category | Endpoints | Total |
|----------|-----------|-------|
| **Authentication** | Login, Logout, Refresh, Push Token | 4 |
| **Properties** | Search, Details, Suggestions, Trending | 6 |
| **Wishlist** | Get, Add, Remove, Recent, Compare | 5 |
| **Finance** | EMI Calc, Bank Rates, Affordability, Prepayment | 4 |
| **Site Visits** | Slots, Book, My Visits, Cancel | 4 |
| **Alerts** | Price Alert, Property Alert | 3 |
| **User** | Profile, Bookings, EMI Schedule | 3 |
| **MLM** | Summary, Payouts, Genealogy | 6 |
| **Location** | States, Districts, Areas | 3 |
| **Upload** | Document Upload | 1 |

**Total: 40+ API Endpoints**

---

## 🔧 FLUTTER INTEGRATION EXAMPLE

### API Service Class
```dart
class ApiService {
  static const String baseUrl = 'https://apsdreamhome.com/api';
  static String? _token;

  static Future<Map<String, dynamic>> login(String email, String password) async {
    final response = await http.post(
      Uri.parse('$baseUrl/v2/mobile/auth/login'),
      body: jsonEncode({'email': email, 'password': password}),
      headers: {'Content-Type': 'application/json'},
    );
    
    final data = jsonDecode(response.body);
    if (data['success']) {
      _token = data['access_token'];
      await saveToken(_token!);
    }
    return data;
  }

  static Future<Map<String, dynamic>> searchProperties(Map<String, dynamic> filters) async {
    final queryString = Uri(queryParameters: filters).query;
    final response = await http.get(
      Uri.parse('$baseUrl/v1/search/properties?$queryString'),
      headers: {'Authorization': 'Bearer $_token'},
    );
    return jsonDecode(response.body);
  }

  static Future<void> saveToken(String token) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('jwt_token', token);
  }
}
```

---

## 📱 PUSH NOTIFICATIONS

### FCM Integration
1. Register device token after login
2. Server sends push for:
   - Price drop alerts
   - New property matches
   - Site visit reminders
   - Booking confirmations

### Notification Payload
```json
{
  "notification": {
    "title": "Price Drop Alert!",
    "body": "Villa in Surya City dropped to ₹14 Lakhs"
  },
  "data": {
    "type": "price_alert",
    "property_id": "123",
    "click_action": "FLUTTER_NOTIFICATION_CLICK"
  }
}
```

---

## ✅ PRODUCTION CHECKLIST

- [ ] Use HTTPS only
- [ ] Implement token refresh
- [ ] Handle 401 errors (logout user)
- [ ] Add retry logic for network errors
- [ ] Cache search results
- [ ] Implement offline mode
- [ ] Add analytics tracking
- [ ] Test on real devices

---

**API Version:** 2.0  
**Last Updated:** May 2026  
**Support:** api@apsdreamhome.com

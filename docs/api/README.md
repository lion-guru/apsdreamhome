# API Documentation

## Base URL
```
https://api.apsdreamhome.com/v1
```

## Authentication
All API requests require an API key sent in the header:
```
Authorization: Bearer YOUR_API_KEY
```

## Endpoints

### 1. Authentication

#### Login
```
POST /auth/login
```
**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "yourpassword"
}
```
**Response:**
```json
{
  "token": "jwt_token_here",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com",
    "role": "admin"
  }
}
```

### 2. Properties

#### List Properties
```
GET /properties
```
**Query Parameters:**
- `type` - Filter by property type
- `status` - Filter by status (available, sold, etc.)
- `min_price` - Minimum price
- `max_price` - Maximum price

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "title": "Luxury Villa",
      "type": "Villa",
      "price": 2500000,
      "status": "available",
      "location": "Mumbai"
    }
  ],
  "pagination": {
    "total": 1,
    "per_page": 15,
    "current_page": 1,
    "last_page": 1
  }
}
```

### 3. Bookings

#### Create Booking
```
POST /bookings
```
**Request Body:**
```json
{
  "property_id": 1,
  "customer_id": 1,
  "booking_date": "2025-06-01",
  "amount": 50000,
  "payment_plan": "installment"
}
```

### 4. Customers

#### Create Customer
```
POST /customers
```
**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "+911234567890",
  "address": "123 Main St, Mumbai",
  "pan_number": "ABCDE1234F"
}
```

## Error Responses

### 400 Bad Request
```json
{
  "error": "Validation failed",
  "errors": {
    "email": ["The email field is required."]
  }
}
```

### 401 Unauthorized
```json
{
  "error": "Unauthenticated"
}
```

### 404 Not Found
```json
{
  "error": "Resource not found"
}
```

## Rate Limiting
- 60 requests per minute per IP
- 1000 requests per day per API key

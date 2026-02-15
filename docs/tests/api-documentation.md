# API Documentation

## 🔗 API Endpoints

### Admin Dashboard APIs

#### get_dashboard_stats.php

**Method:** GET
**Description:** Retrieves real-time dashboard statistics
**Authentication:** Required
**Response Format:**
```json
{
  "users_count": "value",
  "properties_count": "value",
  "bookings_count": "value",
  "revenue": "value",
  "status": "success"
}
```

**Status:** ✅ File exists and accessible
**Location:** `admin/ajax/get_dashboard_stats.php`
**Size:** 2264 bytes

#### get_analytics_data.php

**Method:** GET
**Description:** Provides analytics data for charts
**Authentication:** Required
**Response Format:**
```json
{
  "labels": "value",
  "datasets": "value",
  "chart_data": "value",
  "status": "success"
}
```

**Status:** ✅ File exists and accessible
**Location:** `admin/ajax/get_analytics_data.php`
**Size:** 2644 bytes

#### global_search.php

**Method:** GET/POST
**Description:** Performs global search across entities
**Authentication:** Required
**Response Format:**
```json
{
  "results": "value",
  "total_count": "value",
  "search_time": "value",
  "status": "success"
}
```

**Status:** ✅ File exists and accessible
**Location:** `admin/ajax/global_search.php`
**Size:** 3620 bytes

#### get_system_status.php

**Method:** GET
**Description:** Returns system health status
**Authentication:** Required
**Response Format:**
```json
{
  "database_status": "value",
  "api_status": "value",
  "server_info": "value",
  "status": "success"
}
```

**Status:** ✅ File exists and accessible
**Location:** `admin/ajax/get_system_status.php`
**Size:** 2293 bytes

#### get_recent_activity.php

**Method:** GET
**Description:** Fetches recent activity feed
**Authentication:** Required
**Response Format:**
```json
{
  "activities": "value",
  "timestamps": "value",
  "user_actions": "value",
  "status": "success"
}
```

**Status:** ✅ File exists and accessible
**Location:** `admin/ajax/get_recent_activity.php`
**Size:** 4161 bytes

#### export_dashboard_data.php

**Method:** POST
**Description:** Exports dashboard data in various formats
**Authentication:** Required
**Response Format:**
```json
{
  "download_url": "value",
  "file_info": "value",
  "export_stats": "value",
  "status": "success"
}
```

**Status:** ✅ File exists and accessible
**Location:** `admin/ajax/export_dashboard_data.php`
**Size:** 6443 bytes

### API Security

#### Authentication
- Session-based authentication required for all APIs
- Admin role validation for dashboard endpoints
- CSRF protection implemented
- Rate limiting considerations

#### Data Validation
- Input sanitization for all parameters
- SQL injection prevention via prepared statements
- XSS protection in response data
- Output encoding for JSON responses

#### Error Handling
- Consistent error response format
- HTTP status codes for different error types
- Detailed error logging for debugging
- User-friendly error messages

### API Usage Examples

#### JavaScript Example
```javascript
// Fetch dashboard statistics
fetch('/admin/ajax/get_dashboard_stats.php', {
  method: 'GET',
  headers: {
    'Content-Type': 'application/json'
  }
})
.then(response => response.json())
.then(data => {
  console.log('Dashboard Stats:', data);
  // Update UI with data
})
.catch(error => {
  console.error('Error:', error);
});
```

#### PHP Example
```php
// API call using cURL
 = curl_init();
curl_setopt(, CURLOPT_URL, '/admin/ajax/get_dashboard_stats.php');
curl_setopt(, CURLOPT_RETURNTRANSFER, true);
curl_setopt(, CURLOPT_COOKIE, session_name() . '=' . session_id());

 = curl_exec();
 = json_decode(, true);

if ($data['status'] === 'success') {
    // Process successful response
    echo 'Users: ' . $data['users_count'];
} else {
    // Handle error
    echo 'Error: ' . $data['message'];
}
```

---

*Last Updated: 2025-11-28 18:46:55*

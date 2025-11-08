<?php
/**
 * Property Search API
 * 
 * This endpoint allows searching for properties based on various criteria
 * including location, price range, property type, and features.
 * 
 * @description Search for properties in the system based on specified criteria.
 * @methods GET
 * 
 * @param {string} location Location to search for properties (city, area, or zip code)
 * @param {number} min_price [optional] Minimum price for filtering properties
 * @param {number} max_price [optional] Maximum price for filtering properties
 * @param {string} property_type [optional] Type of property (apartment, house, villa, etc.)
 * @param {number} bedrooms [optional] Minimum number of bedrooms
 * @param {number} bathrooms [optional] Minimum number of bathrooms
 * @param {string} sort_by [optional] Field to sort results by (price, date, area)
 * @param {string} sort_order [optional] Order of sorting (asc, desc)
 * @param {number} limit [optional] Maximum number of results to return (default: 10)
 * @param {number} offset [optional] Offset for pagination (default: 0)
 * 
 * @response {200} JSON array of properties matching the search criteria
 * @response {400} Invalid parameters provided
 * @response {401} API key is missing or invalid
 * @response {403} API key does not have permission to access this endpoint
 * @response {404} No properties found matching the criteria
 * @response {429} Rate limit exceeded
 * @response {500} Server error occurred
 * 
 * @example
 * // Search for properties in New York with price between $200,000 and $500,000
 * GET /api/properties/search.php?location=New%20York&min_price=200000&max_price=500000
 * 
 * // Response:
 * {
 *   "status": "success",
 *   "count": 2,
 *   "properties": [
 *     {
 *       "id": 1,
 *       "title": "Modern Apartment in Downtown",
 *       "price": 350000,
 *       "address": "123 Main St, New York, NY",
 *       "bedrooms": 2,
 *       "bathrooms": 1,
 *       "area": 1200,
 *       "type": "apartment",
 *       "image": "/images/properties/property1.jpg"
 *     },
 *     {
 *       "id": 2,
 *       "title": "Spacious Family Home",
 *       "price": 450000,
 *       "address": "456 Oak Ave, New York, NY",
 *       "bedrooms": 3,
 *       "bathrooms": 2,
 *       "area": 2000,
 *       "type": "house",
 *       "image": "/images/properties/property2.jpg"
 *     }
 *   ]
 * }
 */

// Include authentication middleware
require_once __DIR__ . '/../auth/middleware.php';

// Set header for JSON response
header('Content-Type: application/json');

// Handle CORS for cross-origin requests
handleCors();

// Authenticate API request (optional for public search endpoint)
$auth = authenticateApiRequest(false);

// Connect to database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "apsdreamhome";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection failed'
    ]);
    exit;
}

// Get and validate parameters
$location = isset($_GET['location']) ? $_GET['location'] : '';
$minPrice = isset($_GET['min_price']) ? (int)$_GET['min_price'] : 0;
$maxPrice = isset($_GET['max_price']) ? (int)$_GET['max_price'] : PHP_INT_MAX;
$propertyType = isset($_GET['property_type']) ? $_GET['property_type'] : '';
$bedrooms = isset($_GET['bedrooms']) ? (int)$_GET['bedrooms'] : 0;
$bathrooms = isset($_GET['bathrooms']) ? (int)$_GET['bathrooms'] : 0;
$sortBy = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'price';
$sortOrder = isset($_GET['sort_order']) ? strtoupper($_GET['sort_order']) : 'ASC';
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

// Validate parameters
if ($limit <= 0 || $limit > 100) {
    $limit = 10;
}

if ($offset < 0) {
    $offset = 0;
}

if (!in_array($sortBy, ['price', 'date', 'area'])) {
    $sortBy = 'price';
}

if (!in_array($sortOrder, ['ASC', 'DESC'])) {
    $sortOrder = 'ASC';
}

// Build query
$sql = "SELECT * FROM properties WHERE 1=1";

// Add filters
if (!empty($location)) {
    $location = $conn->real_escape_string($location);
    $sql .= " AND (address LIKE '%$location%' OR city LIKE '%$location%' OR zip_code LIKE '%$location%')";
}

if ($minPrice > 0) {
    $sql .= " AND price >= $minPrice";
}

if ($maxPrice < PHP_INT_MAX) {
    $sql .= " AND price <= $maxPrice";
}

if (!empty($propertyType)) {
    $propertyType = $conn->real_escape_string($propertyType);
    $sql .= " AND type = '$propertyType'";
}

if ($bedrooms > 0) {
    $sql .= " AND bedrooms >= $bedrooms";
}

if ($bathrooms > 0) {
    $sql .= " AND bathrooms >= $bathrooms";
}

// Add sorting
switch ($sortBy) {
    case 'date':
        $sql .= " ORDER BY created_at $sortOrder";
        break;
    case 'area':
        $sql .= " ORDER BY area $sortOrder";
        break;
    case 'price':
    default:
        $sql .= " ORDER BY price $sortOrder";
        break;
}

// Add pagination
$sql .= " LIMIT $limit OFFSET $offset";

// Execute query
$result = $conn->query($sql);

// Check for errors
if (!$result) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database query failed: ' . $conn->error
    ]);
    exit;
}

// Process results
$properties = [];
while ($row = $result->fetch_assoc()) {
    // Format property data
    $properties[] = [
        'id' => (int)$row['id'],
        'title' => $row['title'],
        'price' => (float)$row['price'],
        'address' => $row['address'],
        'bedrooms' => (int)$row['bedrooms'],
        'bathrooms' => (float)$row['bathrooms'],
        'area' => (float)$row['area'],
        'type' => $row['type'],
        'image' => isset($row['image']) ? $row['image'] : null
    ];
}

// Return response
if (empty($properties)) {
    http_response_code(404);
    echo json_encode([
        'status' => 'error',
        'message' => 'No properties found matching the criteria'
    ]);
} else {
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'count' => count($properties),
        'properties' => $properties
    ]);
}

// Close connection
$conn->close();
?>

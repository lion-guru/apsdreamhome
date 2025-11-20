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
$params = [];
$types = "";

// Add filters
if (!empty($location)) {
    $sql .= " AND (address LIKE ? OR city LIKE ? OR zip_code LIKE ?)";
    $locationParam = "%" . $location . "%";
    array_push($params, $locationParam, $locationParam, $locationParam);
    $types .= "sss";
}

if ($minPrice > 0) {
    $sql .= " AND price >= ?";
    $params[] = $minPrice;
    $types .= "i";
}

if ($maxPrice < PHP_INT_MAX) {
    $sql .= " AND price <= ?";
    $params[] = $maxPrice;
    $types .= "i";
}

if (!empty($propertyType)) {
    $sql .= " AND type = ?";
    $params[] = $propertyType;
    $types .= "s";
}

if ($bedrooms > 0) {
    $sql .= " AND bedrooms >= ?";
    $params[] = $bedrooms;
    $types .= "i";
}

if ($bathrooms > 0) {
    $sql .= " AND bathrooms >= ?";
    $params[] = $bathrooms;
    $types .= "i";
}

// Add sorting
$sql .= " ORDER BY $sortBy $sortOrder";

// Add pagination
$sql .= " LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= "ii";

// Execute query
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database query preparation failed'
    ]);
    exit;
}

if (!empty($types) && !empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

// Check for errors
if (!$result) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database query failed'
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

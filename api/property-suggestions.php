<?php
header('Content-Type: application/json');
require_once '../includes/src/Database/Database.php';
require_once '../includes/config.php';

// Input validation and sanitization
$query = isset($_GET['query']) ? trim($_GET['query']) : '';

if (empty($query)) {
    echo json_encode([]);
    exit;
}

// Prepare suggestions query with multiple search criteria
$searchQuery = mysqli_real_escape_string($conn, $query);
$suggestions = [];

// Search by location, property type, and name
$sql = "SELECT 
    id, 
    name, 
    location, 
    property_type, 
    price,
    CASE 
        WHEN location LIKE '%{$searchQuery}%' THEN 1
        WHEN property_type LIKE '%{$searchQuery}%' THEN 2
        WHEN name LIKE '%{$searchQuery}%' THEN 3
        ELSE 4
    END as relevance
FROM properties
WHERE 
    location LIKE '%{$searchQuery}%' OR 
    property_type LIKE '%{$searchQuery}%' OR 
    name LIKE '%{$searchQuery}%'
ORDER BY relevance, price
LIMIT 10";

$result = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_assoc($result)) {
    $suggestions[] = [
        'id' => $row['id'],
        'value' => "{$row['name']} - {$row['location']}",
        'label' => sprintf(
            "%s (%s) - \u20b9%s", 
            htmlspecialchars($row['name']), 
            htmlspecialchars($row['location']), 
            number_format($row['price'])
        ),
        'type' => htmlspecialchars($row['property_type'])
    ];
}

// Fallback to AI-powered suggestions if no direct matches
if (empty($suggestions)) {
    // Implement AI-powered suggestion logic using machine learning model
    $aiSuggestions = getAIPropertySuggestions($query);
    $suggestions = array_merge($suggestions, $aiSuggestions);
}

echo json_encode($suggestions);

/**
 * AI-powered property suggestion function
 * This is a placeholder for advanced machine learning recommendation
 */
function getAIPropertySuggestions($query) {
    // Simulated AI suggestions
    $aiSuggestions = [
        [
            'id' => 'ai_suggestion_1',
            'value' => 'AI Recommended Property',
            'label' => 'AI Suggested Property based on your search',
            'type' => 'AI Recommendation'
        ]
    ];
    return $aiSuggestions;
}

// Optional: Log search queries for future improvements
logSearchQuery($query, count($suggestions));

/**
 * Log search queries for analytics
 */
function logSearchQuery($query, $resultsCount) {
    global $conn;
    $query = mysqli_real_escape_string($conn, $query);
    $logSql = "INSERT INTO search_logs (query, results_count, searched_at) 
               VALUES ('{$query}', {$resultsCount}, NOW())";
    mysqli_query($conn, $logSql);
}

<?php
header('Content-Type: application/json');
require_once '../app/bootstrap.php';

// Input validation and sanitization
$query = isset($_GET['query']) ? trim($_GET['query']) : '';

if (empty($query)) {
    echo json_encode([]);
    exit;
}

$db = \App\Core\App::database();
$suggestions = [];

try {
    // Search by location, property type, and name using named bindings
    $sql = "SELECT 
        id, 
        name, 
        location, 
        property_type, 
        price,
        CASE 
            WHEN location LIKE :query1 THEN 1
            WHEN property_type LIKE :query2 THEN 2
            WHEN name LIKE :query3 THEN 3
            ELSE 4
        END as relevance
    FROM properties
    WHERE 
        location LIKE :query4 OR 
        property_type LIKE :query5 OR 
        name LIKE :query6
    ORDER BY relevance, price
    LIMIT 10";

    $searchTerm = "%$query%";
    $params = [
        'query1' => $searchTerm,
        'query2' => $searchTerm,
        'query3' => $searchTerm,
        'query4' => $searchTerm,
        'query5' => $searchTerm,
        'query6' => $searchTerm
    ];

    $rows = $db->fetch($sql, $params);

    foreach ($rows as $row) {
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

    // Optional: Log search queries for future improvements
    logSearchQuery($query, count($suggestions));

} catch (Exception $e) {
    error_log("Property suggestions error: " . $e->getMessage());
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

/**
 * Log search queries for analytics
 */
function logSearchQuery($query, $resultsCount) {
    try {
        $db = \App\Core\App::database();
        $logSql = "INSERT INTO search_logs (query, results_count, searched_at) 
                   VALUES (:query, :results_count, NOW())";
        $db->execute($logSql, [
            'query' => $query,
            'results_count' => $resultsCount
        ]);
    } catch (Exception $e) {
        error_log("Error logging search query: " . $e->getMessage());
    }
}
?>
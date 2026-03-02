<?php
/**
 * Get Analytics Data - AJAX Endpoint
 * Returns chart data for dashboard analytics
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Include admin configuration
require_once __DIR__ . '/../core/init.php';
require_once __DIR__ . '/../../includes/PropertyAI.php'; // Include PropertyAI

// Verify admin authentication
if (!isAdmin()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => h($mlSupport->translate('Unauthorized'))]);
    exit();
}

// CSRF validation
$csrf_token = $_GET['csrf_token'] ?? '';
if (!verifyCSRFToken($csrf_token)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => h($mlSupport->translate('Security validation failed'))]);
    exit();
}

try {
    $db = \App\Core\App::database();

    $propertyAI = new PropertyAI($db);
    $analyticsData = $propertyAI->getAIAnalytics();

    // Sanitize labels
    $labels = $analyticsData['labels'] ?? [];
    foreach ($labels as &$label) {
        $label = h($label);
    }

    echo json_encode([
        'success' => true,
        'chart_data' => [
            'labels' => $labels,
            'datasets' => $analyticsData['datasets'] ?? []
        ],
        'period' => 'mock' // Indicate mock data
    ]);

} catch (Exception $e) {
    error_log('Analytics data error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => h($mlSupport->translate('Error retrieving analytics data'))
    ]);
}
?>


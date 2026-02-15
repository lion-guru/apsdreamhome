<?php
require_once __DIR__ . '/../../core/init.php';

header('Content-Type: application/json');

try {
    // CSRF Validation
    if (!isset($_GET['csrf_token']) || !verifyCSRFToken($_GET['csrf_token'])) {
        echo json_encode([
            'error' => h($mlSupport->translate('Invalid CSRF token'))
        ]);
        exit;
    }

    // RBAC Protection - Only Super Admin, Admin, and Manager can view property list for accounting
    $currentRole = $_SESSION['admin_role'] ?? '';
    $allowedRoles = ['superadmin', 'admin', 'manager'];
    if (!in_array($currentRole, $allowedRoles)) {
        echo json_encode([
            'error' => h($mlSupport->translate('Unauthorized: You do not have permission to view property list'))
        ]);
        exit;
    }

    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $limit = 10;
    $offset = ($page - 1) * $limit;

    // Base query - only show available or active properties
    $query = "SELECT id, title, type, location
              FROM properties
              WHERE (title LIKE ? OR type LIKE ? OR location LIKE ?)
              AND status = 'available'
              ORDER BY title ASC
              LIMIT ? OFFSET ?";

    $searchTerm = "%$search%";
    $results = \App\Core\App::database()->fetchAll($query, [$searchTerm, $searchTerm, $searchTerm, $limit, $offset]);

    // Count total results for pagination
    $countQuery = "SELECT COUNT(*) as count
                   FROM properties
                   WHERE (title LIKE ? OR type LIKE ? OR location LIKE ?)
                   AND status = 'available'";
    $totalCount = \App\Core\App::database()->fetchOne($countQuery, [$searchTerm, $searchTerm, $searchTerm])['count'];

    $items = [];
    foreach ($results as $row) {
        $items[] = [
            'id' => $row['id'],
            'text' => h($row['title']) . ' (' . h($row['type']) . ' - ' . h($row['location']) . ')'
        ];
    }

    echo json_encode([
        'items' => $items,
        'more' => ($page * $limit) < $totalCount
    ]);

} catch (Exception $e) {
    echo json_encode([
        'error' => h($mlSupport->translate('Failed to fetch properties')) . ': ' . h($e->getMessage())
    ]);
}
?>

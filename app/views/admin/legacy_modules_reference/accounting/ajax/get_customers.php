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

    // RBAC Protection - Only Super Admin, Admin, and Manager can view customer list for accounting
    $currentRole = $_SESSION['admin_role'] ?? '';
    $allowedRoles = ['superadmin', 'admin', 'manager'];
    if (!in_array($currentRole, $allowedRoles)) {
        echo json_encode([
            'error' => h($mlSupport->translate('Unauthorized: You do not have permission to view customer list'))
        ]);
        exit;
    }

    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $limit = 10;
    $offset = ($page - 1) * $limit;

    // Base query
    $query = "SELECT id, name, email, phone
              FROM customers
              WHERE (name LIKE ? OR email LIKE ? OR phone LIKE ?)
              ORDER BY name ASC
              LIMIT ? OFFSET ?";

    $searchTerm = "%$search%";
    $results = \App\Core\App::database()->fetchAll($query, [$searchTerm, $searchTerm, $searchTerm, $limit, $offset]);

    // Count total results for pagination
    $countQuery = "SELECT COUNT(*) as count
                   FROM customers
                   WHERE (name LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $totalCount = \App\Core\App::database()->fetchOne($countQuery, [$searchTerm, $searchTerm, $searchTerm])['count'];

    $items = [];
    foreach ($results as $row) {
        $items[] = [
            'id' => $row['id'],
            'text' => h($row['name']) . ' (' . h($row['phone']) . ')'
        ];
    }

    echo json_encode([
        'items' => $items,
        'more' => ($page * $limit) < $totalCount
    ]);

} catch (Exception $e) {
    echo json_encode([
        'error' => h($mlSupport->translate('Failed to fetch customers')) . ': ' . h($e->getMessage())
    ]);
}
?>

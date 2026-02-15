<?php
require_once '../../../includes/config.php';
require_once '../../../includes/auth_check.php';

header('Content-Type: application/json');

try {
    global $con;
    $conn = $con;
    
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
              
    $stmt = $conn->prepare($query);
    $searchTerm = "%$search%";
    $stmt->bind_param("sssii", $searchTerm, $searchTerm, $searchTerm, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Count total results for pagination
    $countQuery = "SELECT COUNT(*) as count 
                   FROM customers 
                   WHERE (name LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $stmt = $conn->prepare($countQuery);
    $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
    $stmt->execute();
    $totalCount = $stmt->get_result()->fetch_assoc()['count'];
    
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = [
            'id' => $row['id'],
            'text' => $row['name'] . ' (' . $row['phone'] . ')'
        ];
    }
    
    echo json_encode([
        'items' => $items,
        'more' => ($page * $limit) < $totalCount
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'error' => 'Failed to fetch customers: ' . $e->getMessage()
    ]);
}
?>

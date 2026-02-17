<?php
header('Content-Type: application/json');

// Include admin core initialization
require_once dirname(__DIR__, 2) . '/core/init.php';

// CSRF Validation
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    echo json_encode([
        'success' => false,
        'message' => h($mlSupport->translate('Invalid CSRF token'))
    ]);
    exit;
}

// RBAC Protection - Only Super Admin, Admin, and Manager can view transactions
$currentRole = $_SESSION['admin_role'] ?? '';
$allowedRoles = ['superadmin', 'admin', 'manager'];
if (!in_array($currentRole, $allowedRoles)) {
    echo json_encode([
        'success' => false,
        'message' => h($mlSupport->translate('Unauthorized: You do not have permission to view transactions'))
    ]);
    exit;
}

try {
    // $db is already initialized in core/init.php

    // Parameters from DataTables
    $draw = isset($_POST['draw']) ? intval($_POST['draw']) : 1;
    $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
    $length = isset($_POST['length']) ? intval($_POST['length']) : 10;
    $search = isset($_POST['search']['value']) ? $_POST['search']['value'] : '';
    $orderColumn = isset($_POST['order'][0]['column']) ? intval($_POST['order'][0]['column']) : 0;
    $orderDir = isset($_POST['order'][0]['dir']) && in_array(strtolower($_POST['order'][0]['dir']), ['asc', 'desc']) ? $_POST['order'][0]['dir'] : 'DESC';

    // Column names for ordering
    $columns = ['payment_date', 'transaction_id', 'payment_type', 'description', 'amount', 'status'];
    $orderBy = $columns[$orderColumn] ?? $columns[0];

    // Base query
    $query = "SELECT p.*, c.name as customer_name
              FROM payments p
              LEFT JOIN customers c ON p.customer_id = c.id";

    // Search condition
    $searchCondition = "";
    $params = [];
    if (!empty($search)) {
        $searchCondition = " WHERE (p.transaction_id LIKE ?
                            OR p.description LIKE ?
                            OR c.name LIKE ?
                            OR p.amount LIKE ?)";
        $query .= $searchCondition;
        $searchParam = "%$search%";
        $params = [$searchParam, $searchParam, $searchParam, $searchParam];
    }

    // Count total records
    $totalQuery = "SELECT COUNT(*) as count FROM payments p LEFT JOIN customers c ON p.customer_id = c.id";
    $totalRecords = $db->fetch($totalQuery)['count'];

    // Count filtered records
    if (!empty($searchCondition)) {
        $filteredQuery = "SELECT COUNT(*) as count FROM payments p LEFT JOIN customers c ON p.customer_id = c.id" . $searchCondition;
        $filteredRecords = $db->fetch($filteredQuery, $params)['count'];
    } else {
        $filteredRecords = $totalRecords;
    }

    // Get the actual data
    $query .= " ORDER BY $orderBy $orderDir LIMIT ?, ?";
    $dataParams = array_merge($params, [$start, $length]);

    $results = $db->fetchAll($query, $dataParams);
    $data = [];

    foreach ($results as $row) {
        // Format the data
        $row_id = intval($row['id']);
        $actions = '<button onclick="viewTransaction('.$row_id.')" class="btn btn-info btn-sm mr-1" title="' . h($mlSupport->translate('View')) . '"><i class="fas fa-eye"></i></button>';
        $actions .= '<button onclick="editTransaction('.$row_id.')" class="btn btn-primary btn-sm mr-1" title="' . h($mlSupport->translate('Edit')) . '"><i class="fas fa-edit"></i></button>';
        $actions .= '<button onclick="deleteTransaction('.$row_id.')" class="btn btn-danger btn-sm" title="' . h($mlSupport->translate('Delete')) . '"><i class="fas fa-trash"></i></button>';

        $statusBadge = '';
        $status = strtolower($row['status']);
        switch($status) {
            case 'completed':
                $statusBadge = '<span class="badge badge-success">' . h($mlSupport->translate(ucfirst($status))) . '</span>';
                break;
            case 'pending':
                $statusBadge = '<span class="badge badge-warning">' . h($mlSupport->translate(ucfirst($status))) . '</span>';
                break;
            case 'failed':
                $statusBadge = '<span class="badge badge-danger">' . h($mlSupport->translate(ucfirst($status))) . '</span>';
                break;
            default:
                $statusBadge = '<span class="badge badge-secondary">' . h($mlSupport->translate(ucfirst($status))) . '</span>';
                break;
        }

        $data[] = [
            'date' => h(date('d M Y', strtotime($row['payment_date']))),
            'transaction_id' => h($row['transaction_id']),
            'customer_name' => h($row['customer_name'] ?? '—'),
            'type' => h($mlSupport->translate(ucfirst($row['payment_type']))),
            'description' => h($row['description']),
            'amount' => '₹' . h(number_format((float)$row['amount'], 2)),
            'status' => $statusBadge,
            'actions' => $actions
        ];
    }

    echo json_encode([
        'draw' => $draw,
        'recordsTotal' => $totalRecords,
        'recordsFiltered' => $filteredRecords,
        'data' => $data
    ]);

} catch (Exception $e) {
    echo json_encode([
        'error' => h($mlSupport->translate('Failed to fetch transactions')) . ': ' . h($e->getMessage())
    ]);
}
?>

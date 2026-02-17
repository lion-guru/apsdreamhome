<?php
require_once __DIR__ . '/../../core/init.php';

header('Content-Type: application/json');

// CSRF Validation
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    echo json_encode([
        'success' => false,
        'message' => h($mlSupport->translate('Invalid CSRF token'))
    ]);
    exit;
}

// RBAC Protection - Only Super Admin, Admin, and Manager can view payments
$currentRole = $_SESSION['admin_role'] ?? '';
$allowedRoles = ['superadmin', 'admin', 'manager'];
if (!in_array($currentRole, $allowedRoles)) {
    echo json_encode([
        'success' => false,
        'message' => h($mlSupport->translate('Unauthorized: You do not have permission to view payments'))
    ]);
    exit;
}

try {
    // DataTables parameters
    $draw = isset($_POST['draw']) ? intval($_POST['draw']) : 0;
    $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
    $length = isset($_POST['length']) ? intval($_POST['length']) : 10;
    $searchValue = isset($_POST['search']['value']) ? $_POST['search']['value'] : '';

    // Map DataTables column indexes to DB columns
    // UI order: Date, Transaction ID, Customer, Type, Amount, Status, Actions
    $columns = [
        'p.payment_date',
        'p.transaction_id',
        'c.name',
        'p.payment_type',
        'p.amount',
        'p.status'
    ];

    // Base query
    $baseQuery = "
        FROM payments p
        LEFT JOIN customers c ON p.customer_id = c.id
    ";

    // Filtering
    $whereClauses = [];
    $params = [];

    if ($searchValue !== '') {
        $whereClauses[] = "(p.transaction_id LIKE ? OR p.payment_type LIKE ? OR p.description LIKE ? OR c.name LIKE ?)";
        $like = "%" . $searchValue . "%";
        $params = array_merge($params, [$like, $like, $like, $like]);
    }

    // Add date range filter if present
    if (isset($_POST['dateRange']) && !empty($_POST['dateRange'])) {
        $dates = explode(' - ', $_POST['dateRange']);
        if (count($dates) === 2) {
            $startDate = date('Y-m-d', strtotime($dates[0]));
            $endDate = date('Y-m-d', strtotime($dates[1]));
            $whereClauses[] = "p.payment_date BETWEEN ? AND ?";
            $params[] = $startDate . ' 00:00:00';
            $params[] = $endDate . ' 23:59:59';
        }
    }

    // Add status filter
    if (isset($_POST['status']) && !empty($_POST['status'])) {
        $whereClauses[] = "p.status = ?";
        $params[] = $_POST['status'];
    }

    // Add type filter
    if (isset($_POST['type']) && !empty($_POST['type'])) {
        $whereClauses[] = "p.payment_type = ?";
        $params[] = $_POST['type'];
    }

    $whereSQL = count($whereClauses) ? ('WHERE ' . implode(' AND ', $whereClauses)) : '';

    // Ordering
    $orderColumnIndex = isset($_POST['order'][0]['column']) ? intval($_POST['order'][0]['column']) : 0;
    $orderDir = (isset($_POST['order'][0]['dir']) && strtolower($_POST['order'][0]['dir']) === 'asc') ? 'ASC' : 'DESC';
    $orderBy = isset($columns[$orderColumnIndex]) ? $columns[$orderColumnIndex] : $columns[0];

    // Total records
    $totalQuery = "SELECT COUNT(*) as total FROM payments";
    $totalRow = \App\Core\App::database()->fetchOne($totalQuery);
    $recordsTotal = $totalRow ? intval($totalRow['total']) : 0;

    // Filtered records count
    $countSQL = "SELECT COUNT(*) as filtered " . $baseQuery . " " . $whereSQL;
    $countRow = \App\Core\App::database()->fetchOne($countSQL, $params);
    $recordsFiltered = $countRow ? intval($countRow['filtered']) : $recordsTotal;

    // Data query
    $dataSQL = "
        SELECT
            p.id,
            p.payment_date,
            p.transaction_id,
            p.payment_type,
            p.description,
            p.amount,
            p.status,
            c.name AS customer_name
        " . $baseQuery . "
        " . $whereSQL . "
        ORDER BY $orderBy $orderDir
        LIMIT ?, ?
    ";

    $dataParams = array_merge($params, [$start, $length]);
    $results = \App\Core\App::database()->fetchAll($dataSQL, $dataParams);

    $data = [];
    foreach ($results as $row) {
        $statusBadgeClass = 'bg-secondary';
        switch (strtolower($row['status'])) {
            case 'pending':
                $statusBadgeClass = 'bg-warning text-dark';
                break;
            case 'completed':
                $statusBadgeClass = 'bg-success';
                break;
            case 'failed':
                $statusBadgeClass = 'bg-danger';
                break;
        }

        $statusHtml = '<span class="badge rounded-pill ' . $statusBadgeClass . ' px-3">' . h($mlSupport->translate(ucfirst($row['status']))) . '</span>';

        $actionsHtml = '<div class="btn-group">'
            . '<button class="btn btn-sm btn-outline-primary rounded-circle me-1" onclick="viewPayment(' . intval($row['id']) . ')" title="' . h($mlSupport->translate('View')) . '"><i class="fas fa-eye"></i></button>'
            . '<button class="btn btn-sm btn-outline-danger rounded-circle" onclick="deletePayment(' . intval($row['id']) . ')" title="' . h($mlSupport->translate('Delete')) . '"><i class="fas fa-trash"></i></button>'
            . '</div>';

        $data[] = [
            'payment_date' => h(date('d M Y, h:i A', strtotime($row['payment_date']))),
            'transaction_id' => '<code class="text-primary fw-bold">' . h($row['transaction_id']) . '</code>',
            'customer_name' => $row['customer_name'] ? '<span class="fw-bold">' . h($row['customer_name']) . '</span>' : '—',
            'payment_type' => '<span class="badge bg-light text-dark border">' . h($mlSupport->translate(ucfirst($row['payment_type']))) . '</span>',
            'description' => h($row['description']),
            'amount' => '<span class="fw-bold text-dark">₹' . h(number_format((float)$row['amount'], 2)) . '</span>',
            'status' => $statusHtml,
            'actions' => $actionsHtml,
        ];
    }

    echo json_encode([
        'draw' => $draw,
        'recordsTotal' => $recordsTotal,
        'recordsFiltered' => $recordsFiltered,
        'data' => $data
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => h($mlSupport->translate('Failed to fetch payments')) . ': ' . h($e->getMessage())
    ]);
}
exit();
?>

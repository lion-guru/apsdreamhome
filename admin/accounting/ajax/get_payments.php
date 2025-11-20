<?php
require_once '../../../includes/config.php';
require_once '../../../includes/db_connection.php';
require_once '../../../includes/auth_check.php';

// Ensure admin access
if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// DataTables parameters
$draw = isset($_GET['draw']) ? intval($_GET['draw']) : 0;
$start = isset($_GET['start']) ? intval($_GET['start']) : 0;
$length = isset($_GET['length']) ? intval($_GET['length']) : 10;
$searchValue = isset($_GET['search']['value']) ? $_GET['search']['value'] : '';

// Map DataTables column indexes to DB columns
// UI order: Date, Transaction ID, Customer, Type, Amount, Status, Actions
$columns = [
    'p.payment_date',
    'p.transaction_id',
    'c.name', // customer name
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

$whereSQL = count($whereClauses) ? ('WHERE ' . implode(' AND ', $whereClauses)) : '';

// Ordering
$orderColumnIndex = isset($_GET['order'][0]['column']) ? intval($_GET['order'][0]['column']) : 0;
$orderDir = (isset($_GET['order'][0]['dir']) && strtolower($_GET['order'][0]['dir']) === 'asc') ? 'ASC' : 'DESC';
$orderBy = isset($columns[$orderColumnIndex]) ? $columns[$orderColumnIndex] : $columns[0];

// Total records
$totalQuery = "SELECT COUNT(*) as total FROM payments";
$totalResult = $conn->query($totalQuery);
$recordsTotal = ($totalResult && $row = $totalResult->fetch_assoc()) ? intval($row['total']) : 0;

// Filtered records count
$countSQL = "SELECT COUNT(*) as filtered " . $baseQuery . " " . $whereSQL;
$countStmt = $conn->prepare($countSQL);
if ($countStmt && count($params)) {
    $types = str_repeat('s', count($params));
    $countStmt->bind_param($types, ...$params);
}
$countStmt && $countStmt->execute();
$countResult = $countStmt ? $countStmt->get_result() : null;
$recordsFiltered = ($countResult && $row = $countResult->fetch_assoc()) ? intval($row['filtered']) : $recordsTotal;
if ($countStmt) { $countStmt->close(); }

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

$dataStmt = $conn->prepare($dataSQL);
$bindParams = $params;
$bindParams[] = $start;
$bindParams[] = $length;

// Bind types: params (all 's') + start(int) + length(int)
$types = (count($params) ? str_repeat('s', count($params)) : '') . 'ii';
$dataStmt->bind_param($types, ...$bindParams);
$dataStmt->execute();
$result = $dataStmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    // Status badge
    $statusBadgeClass = 'badge-secondary';
    switch (strtolower($row['status'])) {
        case 'pending':
            $statusBadgeClass = 'badge-warning';
            break;
        case 'completed':
            $statusBadgeClass = 'badge-success';
            break;
        case 'failed':
            $statusBadgeClass = 'badge-danger';
            break;
    }

    $statusHtml = '<span class="badge ' . $statusBadgeClass . '">' . htmlspecialchars($row['status']) . '</span>';

    // Actions
    $actionsHtml = '<div class="btn-group" role="group">'
        . '<button class="btn btn-sm btn-info view-payment" data-id="' . intval($row['id']) . '"><i class="fas fa-eye"></i></button>'
        . '<button class="btn btn-sm btn-danger delete-payment" data-id="' . intval($row['id']) . '"><i class="fas fa-trash-alt"></i></button>'
        . '</div>';

    $data[] = [
        'date' => date('d M Y', strtotime($row['payment_date'])),
        'transaction_id' => htmlspecialchars($row['transaction_id']),
        'customer' => $row['customer_name'] !== null && $row['customer_name'] !== '' ? htmlspecialchars($row['customer_name']) : '—',
        'type' => htmlspecialchars($row['payment_type']),
        'amount' => '₹' . number_format((float)$row['amount'], 2),
        'status' => $statusHtml,
        'actions' => $actionsHtml,
    ];
}

$dataStmt->close();

// Response
echo json_encode([
    'draw' => $draw,
    'recordsTotal' => $recordsTotal,
    'recordsFiltered' => $recordsFiltered,
    'data' => $data
]);

exit();
?>

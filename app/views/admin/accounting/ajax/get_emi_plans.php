<?php
require_once __DIR__ . '/../../core/init.php';

// CSRF Validation
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    echo json_encode([
        'success' => false,
        'message' => h($mlSupport->translate('Invalid CSRF token'))
    ]);
    exit;
}

// RBAC Protection - Only Super Admin, Admin, and Manager can view EMI plans
$currentRole = $_SESSION['admin_role'] ?? '';
$allowedRoles = ['superadmin', 'admin', 'manager'];
if (!in_array($currentRole, $allowedRoles)) {
    echo json_encode([
        'success' => false,
        'message' => h($mlSupport->translate('Unauthorized: You do not have permission to view EMI plans'))
    ]);
    exit;
}

header('Content-Type: application/json');

try {
    // Parameters from DataTables
    $draw = isset($_POST['draw']) ? intval($_POST['draw']) : 1;
    $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
    $length = isset($_POST['length']) ? intval($_POST['length']) : 10;
    $search = isset($_POST['search']['value']) ? $_POST['search']['value'] : '';
    $orderColumn = isset($_POST['order'][0]['column']) ? intval($_POST['order'][0]['column']) : 0;
    $orderDir = isset($_POST['order'][0]['dir']) && in_array(strtolower($_POST['order'][0]['dir']), ['asc', 'desc']) ? $_POST['order'][0]['dir'] : 'DESC';

    // Column names for ordering
    $columns = ['c.name', 'p.title', 'ep.total_amount', 'ep.emi_amount', 'ep.tenure_months', 'ep.start_date', 'ep.status'];
    $orderBy = $columns[$orderColumn] ?? $columns[0];

    // Base query
    $query = "SELECT ep.*, c.name as customer_name, p.title as property_title
              FROM emi_plans ep
              LEFT JOIN customers c ON ep.customer_id = c.id
              LEFT JOIN properties p ON ep.property_id = p.id";

    // Search condition
    $searchCondition = "";
    if (!empty($search)) {
        $searchCondition = " WHERE (c.name LIKE ?
                            OR p.title LIKE ?
                            OR ep.total_amount LIKE ?
                            OR ep.status LIKE ?)";
        $query .= $searchCondition;
    }

    // Count total records
    $totalQuery = "SELECT COUNT(*) as count FROM emi_plans ep";
    $totalRecords = \App\Core\App::database()->fetchOne($totalQuery)['count'];

    // Count filtered records
    $filteredQuery = "SELECT COUNT(*) as count
                     FROM emi_plans ep
                     LEFT JOIN customers c ON ep.customer_id = c.id
                     LEFT JOIN properties p ON ep.property_id = p.id";
    if (!empty($searchCondition)) {
        $filteredQuery .= $searchCondition;
        $searchParam = "%$search%";
        $filteredRecords = \App\Core\App::database()->fetchOne($filteredQuery, [$searchParam, $searchParam, $searchParam, $searchParam])['count'];
    } else {
        $filteredRecords = $totalRecords;
    }

    // Get the actual data
    $query .= " ORDER BY $orderBy $orderDir LIMIT ?, ?";

    if (!empty($search)) {
        $searchParam = "%$search%";
        $results = \App\Core\App::database()->fetchAll($query, [$searchParam, $searchParam, $searchParam, $searchParam, $start, $length]);
    } else {
        $results = \App\Core\App::database()->fetchAll($query, [$start, $length]);
    }

    $data = [];
    foreach ($results as $row) {
        // Get progress information
        $progressQuery = "SELECT
                            COUNT(*) as total_installments,
                            SUM(CASE WHEN payment_status = 'paid' THEN 1 ELSE 0 END) as paid_installments
                         FROM emi_installments
                         WHERE emi_plan_id = ?";
        $progress = \App\Core\App::database()->fetchOne($progressQuery, [$row['id']]);

        // Calculate progress percentage
        $progressPercent = ($progress['total_installments'] > 0)
            ? round(($progress['paid_installments'] / $progress['total_installments']) * 100)
            : 0;

        // Format the status badge
        $statusBadge = '';
        $statusText = $mlSupport->translate($row['status']);
        switch($row['status']) {
            case 'active':
                $statusBadge = '<span class="badge rounded-pill bg-success px-3">' . h($statusText) . '</span>';
                break;
            case 'completed':
                $statusBadge = '<span class="badge rounded-pill bg-primary px-3">' . h($statusText) . '</span>';
                break;
            case 'defaulted':
                $statusBadge = '<span class="badge rounded-pill bg-danger px-3">' . h($statusText) . '</span>';
                break;
            case 'cancelled':
                $statusBadge = '<span class="badge rounded-pill bg-secondary px-3">' . h($statusText) . '</span>';
                break;
        }

        // Format action buttons
        $row_id = intval($row['id']);
        $actions = '<div class="btn-group">';
        $actions .= '<button onclick="viewEMIPlan('.$row_id.')" class="btn btn-outline-info btn-sm rounded-circle p-2 me-1" title="' . h($mlSupport->translate('View Details')) . '"><i class="fas fa-eye"></i></button>';
        $actions .= '<button onclick="manageInstallments('.$row_id.')" class="btn btn-outline-success btn-sm rounded-circle p-2 me-1" title="' . h($mlSupport->translate('Manage Installments')) . '"><i class="fas fa-list-ul"></i></button>';
        if ($row['status'] === 'active') {
            $actions .= '<button onclick="editEMIPlan('.$row_id.')" class="btn btn-outline-primary btn-sm rounded-circle p-2 me-1" title="' . h($mlSupport->translate('Edit')) . '"><i class="fas fa-edit"></i></button>';
            $actions .= '<button onclick="deleteEMIPlan('.$row_id.')" class="btn btn-outline-danger btn-sm rounded-circle p-2" title="' . h($mlSupport->translate('Delete')) . '"><i class="fas fa-trash"></i></button>';
        }
        $actions .= '</div>';

        // Format the data
        $data[] = [
            'customer' => h($row['customer_name']),
            'property' => h($row['property_title']),
            'total_amount' => '₹' . h(number_format((float)$row['total_amount'], 2)),
            'emi_amount' => '₹' . h(number_format((float)$row['emi_amount'], 2)),
            'tenure' => h($row['tenure_months']) . ' ' . h($mlSupport->translate('months')) . '<br>' .
                       '<div class="progress mt-1" style="height: 5px;">
                            <div class="progress-bar" role="progressbar" style="width: '.h($progressPercent).'%;"
                                 aria-valuenow="'.h($progressPercent).'" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <small class="text-muted">'.h($progress['paid_installments']).'/'.h($progress['total_installments']).' ' . h($mlSupport->translate('paid')) . '</small>',
            'start_date' => h(date('d M Y', strtotime($row['start_date']))),
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
        'success' => false,
        'message' => h($mlSupport->translate('Failed to fetch EMI plans')) . ': ' . h($e->getMessage())
    ]);
}
?>

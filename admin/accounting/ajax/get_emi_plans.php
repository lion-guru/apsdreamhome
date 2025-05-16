<?php
require_once '../../../includes/config.php';
require_once '../../../includes/db_connection.php';
require_once '../../../includes/auth_check.php';

header('Content-Type: application/json');

try {
    $conn = getDbConnection();
    
    // Parameters from DataTables
    $draw = isset($_POST['draw']) ? intval($_POST['draw']) : 1;
    $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
    $length = isset($_POST['length']) ? intval($_POST['length']) : 10;
    $search = isset($_POST['search']['value']) ? $_POST['search']['value'] : '';
    $orderColumn = isset($_POST['order'][0]['column']) ? intval($_POST['order'][0]['column']) : 0;
    $orderDir = isset($_POST['order'][0]['dir']) ? $_POST['order'][0]['dir'] : 'DESC';
    
    // Column names for ordering
    $columns = ['c.name', 'p.title', 'ep.total_amount', 'ep.emi_amount', 'ep.tenure_months', 'ep.start_date', 'ep.status'];
    $orderBy = $columns[$orderColumn];
    
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
    $totalRecords = $conn->query($totalQuery)->fetch_assoc()['count'];
    
    // Count filtered records
    $filteredQuery = "SELECT COUNT(*) as count 
                     FROM emi_plans ep
                     LEFT JOIN customers c ON ep.customer_id = c.id
                     LEFT JOIN properties p ON ep.property_id = p.id";
    if (!empty($searchCondition)) {
        $filteredQuery .= $searchCondition;
        $stmt = $conn->prepare($filteredQuery);
        $searchParam = "%$search%";
        $stmt->bind_param("ssss", $searchParam, $searchParam, $searchParam, $searchParam);
        $stmt->execute();
        $filteredRecords = $stmt->get_result()->fetch_assoc()['count'];
    } else {
        $filteredRecords = $totalRecords;
    }
    
    // Get the actual data
    $query .= " ORDER BY $orderBy $orderDir LIMIT ?, ?";
    $stmt = $conn->prepare($query);
    
    if (!empty($search)) {
        $searchParam = "%$search%";
        $stmt->bind_param("ssssii", $searchParam, $searchParam, $searchParam, $searchParam, $start, $length);
    } else {
        $stmt->bind_param("ii", $start, $length);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $data = [];
    
    while ($row = $result->fetch_assoc()) {
        // Get progress information
        $progressQuery = "SELECT 
                            COUNT(*) as total_installments,
                            SUM(CASE WHEN payment_status = 'paid' THEN 1 ELSE 0 END) as paid_installments
                         FROM emi_installments 
                         WHERE emi_plan_id = ?";
        $stmt = $conn->prepare($progressQuery);
        $stmt->bind_param("i", $row['id']);
        $stmt->execute();
        $progress = $stmt->get_result()->fetch_assoc();
        
        // Calculate progress percentage
        $progressPercent = ($progress['total_installments'] > 0) 
            ? round(($progress['paid_installments'] / $progress['total_installments']) * 100) 
            : 0;
        
        // Format the status badge
        $statusBadge = '';
        switch($row['status']) {
            case 'active':
                $statusBadge = '<span class="badge badge-success">Active</span>';
                break;
            case 'completed':
                $statusBadge = '<span class="badge badge-primary">Completed</span>';
                break;
            case 'defaulted':
                $statusBadge = '<span class="badge badge-danger">Defaulted</span>';
                break;
            case 'cancelled':
                $statusBadge = '<span class="badge badge-secondary">Cancelled</span>';
                break;
        }
        
        // Format action buttons
        $actions = '<button onclick="viewEMIPlan('.$row['id'].')" class="btn btn-info btn-sm mr-1" title="View Details"><i class="fas fa-eye"></i></button>';
        if ($row['status'] === 'active') {
            $actions .= '<button onclick="editEMIPlan('.$row['id'].')" class="btn btn-primary btn-sm mr-1" title="Edit"><i class="fas fa-edit"></i></button>';
            $actions .= '<button onclick="deleteEMIPlan('.$row['id'].')" class="btn btn-danger btn-sm" title="Delete"><i class="fas fa-trash"></i></button>';
        }
        
        // Format the data
        $data[] = [
            'customer' => $row['customer_name'],
            'property' => $row['property_title'],
            'total_amount' => '₹' . number_format($row['total_amount'], 2),
            'emi_amount' => '₹' . number_format($row['emi_amount'], 2),
            'tenure' => $row['tenure_months'] . ' months<br>' .
                       '<div class="progress" style="height: 5px;">
                            <div class="progress-bar" role="progressbar" style="width: '.$progressPercent.'%;" 
                                 aria-valuenow="'.$progressPercent.'" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <small class="text-muted">'.$progress['paid_installments'].'/'.$progress['total_installments'].' paid</small>',
            'start_date' => date('d M Y', strtotime($row['start_date'])),
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
        'error' => 'Failed to fetch EMI plans: ' . $e->getMessage()
    ]);
}
?>

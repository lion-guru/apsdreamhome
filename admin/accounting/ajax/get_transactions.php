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
    $columns = ['payment_date', 'transaction_id', 'payment_type', 'description', 'amount', 'status'];
    $orderBy = $columns[$orderColumn];
    
    // Base query
    $query = "SELECT p.*, c.name as customer_name 
              FROM payments p 
              LEFT JOIN customers c ON p.customer_id = c.id";
    
    // Search condition
    $searchCondition = "";
    if (!empty($search)) {
        $searchCondition = " WHERE (p.transaction_id LIKE ? 
                            OR p.description LIKE ? 
                            OR c.name LIKE ? 
                            OR p.amount LIKE ?)";
        $query .= $searchCondition;
    }
    
    // Count total records
    $totalQuery = "SELECT COUNT(*) as count FROM payments p LEFT JOIN customers c ON p.customer_id = c.id";
    $totalRecords = $conn->query($totalQuery)->fetch_assoc()['count'];
    
    // Count filtered records
    $filteredQuery = $totalQuery;
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
        // Format the data
        $actions = '<button onclick="viewTransaction('.$row['id'].')" class="btn btn-info btn-sm mr-1"><i class="fas fa-eye"></i></button>';
        $actions .= '<button onclick="editTransaction('.$row['id'].')" class="btn btn-primary btn-sm mr-1"><i class="fas fa-edit"></i></button>';
        $actions .= '<button onclick="deleteTransaction('.$row['id'].')" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>';
        
        $statusBadge = '';
        switch($row['status']) {
            case 'completed':
                $statusBadge = '<span class="badge badge-success">Completed</span>';
                break;
            case 'pending':
                $statusBadge = '<span class="badge badge-warning">Pending</span>';
                break;
            case 'failed':
                $statusBadge = '<span class="badge badge-danger">Failed</span>';
                break;
        }
        
        $data[] = [
            'date' => date('d M Y', strtotime($row['payment_date'])),
            'transaction_id' => $row['transaction_id'],
            'type' => ucfirst($row['payment_type']),
            'description' => $row['description'],
            'amount' => '₹' . number_format($row['amount'], 2),
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
        'error' => 'Failed to fetch transactions: ' . $e->getMessage()
    ]);
}
?>

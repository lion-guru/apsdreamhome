<?php
$page_title = "Visit Management - APS Dream Homes Admin";
require_once '../includes/admin_header.php';

// Get visit details if ID is provided
$visit_id = $_GET['id'] ?? null;
$visit = null;
if ($visit_id) {
    $stmt = $conn->prepare("
        SELECT 
            v.*,
            c.name as customer_name,
            c.email as customer_email,
            c.phone as customer_phone,
            p.title as property_title,
            p.address as property_address,
            CONCAT(u.first_name, ' ', u.last_name) as agent_name
        FROM property_visits v
        JOIN customers c ON v.customer_id = c.id
        JOIN properties p ON v.property_id = p.id
        LEFT JOIN users u ON p.owner_id = u.id
        WHERE v.id = ?
    ");
    $stmt->bind_param('i', $visit_id);
    $stmt->execute();
    $visit = $stmt->get_result()->fetch_object();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_status':
                $new_status = $_POST['status'];
                $visit_id = $_POST['visit_id'];
                $feedback = $_POST['feedback'] ?? null;
                $rating = $_POST['rating'] ?? null;
                
                $stmt = $conn->prepare("UPDATE property_visits SET status = ?, feedback = ?, rating = ?, updated_at = NOW() WHERE id = ?");
                $stmt->bind_param('ssii', $new_status, $feedback, $rating, $visit_id);
                if ($stmt->execute()) {
                    $success_message = "Visit status updated successfully!";
                    
                    // Send notification to agent
                    if ($visit->owner_id) {
                        $notification = new NotificationManager();
                        $notification->send([
                            'type' => 'visit_status_update',
                            'user_id' => $visit->owner_id,
                            'title' => 'Visit Status Updated',
                            'message' => "Visit for {$visit->property_title} has been marked as " . ucfirst($new_status),
                            'link' => "/admin/visits.php?id=$visit_id"
                        ]);
                    }
                } else {
                    $error_message = "Error updating visit status.";
                }
                break;
        }
    }
}

// Get visits for listing
$where = [];
$params = [];
$types = '';

if (isset($_GET['status']) && $_GET['status'] !== 'all') {
    $where[] = "v.status = ?";
    $params[] = $_GET['status'];
    $types .= 's';
}

if (isset($_GET['date_from']) && $_GET['date_from']) {
    $where[] = "v.visit_date >= ?";
    $params[] = $_GET['date_from'];
    $types .= 's';
}

if (isset($_GET['date_to']) && $_GET['date_to']) {
    $where[] = "v.visit_date <= ?";
    $params[] = $_GET['date_to'];
    $types .= 's';
}

$where_clause = $where ? "WHERE " . implode(" AND ", $where) : "";

$query = "
    SELECT 
        v.*,
        c.name as customer_name,
        c.email as customer_email,
        c.phone as customer_phone,
        p.title as property_title,
        CONCAT(u.first_name, ' ', u.last_name) as agent_name
    FROM property_visits v
    JOIN customers c ON v.customer_id = c.id
    JOIN properties p ON v.property_id = p.id
    LEFT JOIN users u ON p.owner_id = u.id
    $where_clause
    ORDER BY v.visit_date DESC, v.visit_time DESC
";

$stmt = $conn->prepare($query);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$visits = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get visit statistics
$stats = [
    'total' => $conn->query("SELECT COUNT(*) as count FROM property_visits")->fetch_object()->count,
    'today' => $conn->query("SELECT COUNT(*) as count FROM property_visits WHERE DATE(visit_date) = CURDATE()")->fetch_object()->count,
    'completed' => $conn->query("SELECT COUNT(*) as count FROM property_visits WHERE status = 'completed'")->fetch_object()->count,
    'scheduled' => $conn->query("SELECT COUNT(*) as count FROM property_visits WHERE status = 'scheduled'")->fetch_object()->count
];
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Visit Management</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Visit Management</li>
    </ol>
    
    <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>
    
    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>
    
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <h4 class="mb-0"><?php echo number_format($stats['total']); ?></h4>
                    <div>Total Visits</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <h4 class="mb-0"><?php echo number_format($stats['completed']); ?></h4>
                    <div>Completed Visits</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <h4 class="mb-0"><?php echo number_format($stats['scheduled']); ?></h4>
                    <div>Scheduled Visits</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white mb-4">
                <div class="card-body">
                    <h4 class="mb-0"><?php echo number_format($stats['today']); ?></h4>
                    <div>Today's Visits</div>
                </div>
            </div>
        </div>
    </div>
    
    <?php if ($visit): ?>
        <!-- Visit Details -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-calendar-check me-1"></i>
                Visit Details
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Customer Information</h5>
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($visit->customer_name); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($visit->customer_email); ?></p>
                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($visit->customer_phone); ?></p>
                    </div>
                    <div class="col-md-6">
                        <h5>Property Information</h5>
                        <p><strong>Title:</strong> <?php echo htmlspecialchars($visit->property_title); ?></p>
                        <p><strong>Address:</strong> <?php echo htmlspecialchars($visit->property_address); ?></p>
                        <p><strong>Agent:</strong> <?php echo htmlspecialchars($visit->agent_name); ?></p>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <h5>Visit Information</h5>
                        <p><strong>Date:</strong> <?php echo date('F j, Y', strtotime($visit->visit_date)); ?></p>
                        <p><strong>Time:</strong> <?php echo date('g:i A', strtotime($visit->visit_time)); ?></p>
                        <p><strong>Status:</strong> <span class="badge bg-<?php echo getStatusBadgeClass($visit->status); ?>"><?php echo ucfirst($visit->status); ?></span></p>
                        <?php if ($visit->notes): ?>
                            <p><strong>Notes:</strong> <?php echo nl2br(htmlspecialchars($visit->notes)); ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <h5>Update Status</h5>
                        <form method="POST" class="needs-validation" novalidate>
                            <input type="hidden" name="action" value="update_status">
                            <input type="hidden" name="visit_id" value="<?php echo $visit->id; ?>">
                            
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select" required>
                                    <option value="scheduled" <?php echo $visit->status === 'scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                                    <option value="completed" <?php echo $visit->status === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                    <option value="cancelled" <?php echo $visit->status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    <option value="rescheduled" <?php echo $visit->status === 'rescheduled' ? 'selected' : ''; ?>>Rescheduled</option>
                                    <option value="no_show" <?php echo $visit->status === 'no_show' ? 'selected' : ''; ?>>No Show</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Feedback</label>
                                <textarea name="feedback" class="form-control" rows="3"><?php echo htmlspecialchars($visit->feedback ?? ''); ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Rating</label>
                                <select name="rating" class="form-select">
                                    <option value="">Select Rating</option>
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <option value="<?php echo $i; ?>" <?php echo ($visit->rating ?? 0) === $i ? 'selected' : ''; ?>><?php echo str_repeat('★', $i) . str_repeat('☆', 5 - $i); ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Update Visit</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Visits List -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-calendar me-1"></i>
                Property Visits
            </div>
            <div class="card-body">
                <!-- Filters -->
                <form method="GET" class="row mb-4">
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="all">All Status</option>
                            <option value="scheduled" <?php echo ($_GET['status'] ?? '') === 'scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                            <option value="completed" <?php echo ($_GET['status'] ?? '') === 'completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="cancelled" <?php echo ($_GET['status'] ?? '') === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            <option value="rescheduled" <?php echo ($_GET['status'] ?? '') === 'rescheduled' ? 'selected' : ''; ?>>Rescheduled</option>
                            <option value="no_show" <?php echo ($_GET['status'] ?? '') === 'no_show' ? 'selected' : ''; ?>>No Show</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">From Date</label>
                        <input type="date" name="date_from" class="form-control" value="<?php echo $_GET['date_from'] ?? ''; ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">To Date</label>
                        <input type="date" name="date_to" class="form-control" value="<?php echo $_GET['date_to'] ?? ''; ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="visits.php" class="btn btn-secondary">Reset</a>
                        </div>
                    </div>
                </form>
                
                <!-- Visits Table -->
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Customer</th>
                            <th>Property</th>
                            <th>Agent</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($visits as $visit): ?>
                            <tr>
                                <td>
                                    <?php echo date('M j, Y', strtotime($visit['visit_date'])); ?><br>
                                    <?php echo date('g:i A', strtotime($visit['visit_time'])); ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($visit['customer_name']); ?><br>
                                    <small><?php echo htmlspecialchars($visit['customer_email']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($visit['property_title']); ?></td>
                                <td><?php echo htmlspecialchars($visit['agent_name'] ?? 'No Agent'); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo getStatusBadgeClass($visit['status']); ?>">
                                        <?php echo ucfirst($visit['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="?id=<?php echo $visit['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($visits)): ?>
                            <tr>
                                <td colspan="6" class="text-center">No visits found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
function getStatusBadgeClass($status) {
    return match($status) {
        'scheduled' => 'primary',
        'completed' => 'success',
        'cancelled' => 'danger',
        'rescheduled' => 'warning',
        'no_show' => 'secondary',
        default => 'info'
    };
}

require_once '../includes/admin_footer.php';
?>

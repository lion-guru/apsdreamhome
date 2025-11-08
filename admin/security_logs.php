<?php
session_start();

// Security and access control
require_once __DIR__ . '/../includes/config/security.php';
require_once __DIR__ . '/../includes/db_config.php';

// Check if user is logged in and has admin privileges
adminAccessControl(['superadmin', 'admin']);

// Pagination and filtering
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 50;
$offset = ($page - 1) * $limit;

// Filter options
$event_type = $_GET['event_type'] ?? '';
$username = $_GET['username'] ?? '';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

// Build query
$query = "SELECT * FROM security_logs WHERE 1=1";
$params = [];
$types = '';

if (!empty($event_type)) {
    $query .= " AND event_type = ?";
    $params[] = $event_type;
    $types .= 's';
}

if (!empty($username)) {
    $query .= " AND username = ?";
    $params[] = $username;
    $types .= 's';
}

if (!empty($start_date)) {
    $query .= " AND created_at >= ?";
    $params[] = $start_date;
    $types .= 's';
}

if (!empty($end_date)) {
    $query .= " AND created_at <= ?";
    $params[] = $end_date;
    $types .= 's';
}

$query .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

// Prepare and execute query
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$logs = $result->fetch_all(MYSQLI_ASSOC);

// Get total count for pagination
$countQuery = str_replace("*", "COUNT(*)", substr($query, 0, strpos($query, "ORDER BY")));
$countStmt = $conn->prepare($countQuery);
if (!empty($params)) {
    $countStmt->bind_param(substr($types, 0, -2), ...array_slice($params, 0, -2));
}
$countStmt->execute();
$countResult = $countStmt->get_result();
$totalLogs = $countResult->fetch_array()[0];
$totalPages = ceil($totalLogs / $limit);

// Get unique event types and usernames for filters
$eventTypesStmt = $conn->prepare("SELECT DISTINCT event_type FROM security_logs ORDER BY event_type");
$eventTypesStmt->execute();
$eventTypes = $eventTypesStmt->get_result()->fetch_all(MYSQLI_ASSOC);

$usernamesStmt = $conn->prepare("SELECT DISTINCT username FROM security_logs WHERE username IS NOT NULL ORDER BY username");
$usernamesStmt->execute();
$usernames = $usernamesStmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Security Logs - APS Dream Homes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .log-details {
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .event-badge {
            display: inline-block;
            padding: 0.25em 0.5em;
            font-size: 0.8em;
            font-weight: 600;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 0.25rem;
        }
        .event-login-failed { background-color: #dc3545; color: white; }
        .event-login-success { background-color: #28a745; color: white; }
        .event-security-alert { background-color: #ffc107; color: black; }
    </style>
</head>
<body>
<?php include 'admin_header.php'; ?>
<div class="container-fluid">
    <div class="row">
        <?php include 'admin_sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Security Logs</h1>
            </div>

            <div class="card">
                <div class="card-header">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Event Type</label>
                            <select name="event_type" class="form-control select2">
                                <option value="">All Event Types</option>
                                <?php foreach ($eventTypes as $type): ?>
                                    <option value="<?php echo htmlspecialchars($type['event_type']); ?>" 
                                        <?php echo $event_type === $type['event_type'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($type['event_type']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Username</label>
                            <select name="username" class="form-control select2">
                                <option value="">All Users</option>
                                <?php foreach ($usernames as $user): ?>
                                    <option value="<?php echo htmlspecialchars($user['username']); ?>" 
                                        <?php echo $username === $user['username'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($user['username']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" class="form-control" 
                                   value="<?php echo htmlspecialchars($start_date); ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" class="form-control" 
                                   value="<?php echo htmlspecialchars($end_date); ?>">
                        </div>
                        <div class="col-md-2 align-self-end">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="security_logs.php" class="btn btn-secondary">Reset</a>
                        </div>
                    </form>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Timestamp</th>
                                    <th>Event Type</th>
                                    <th>Username</th>
                                    <th>IP Address</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($log['created_at']); ?></td>
                                    <td>
                                        <span class="event-badge event-<?php 
                                            echo strtolower(str_replace('_', '-', htmlspecialchars($log['event_type']))); 
                                        ?>">
                                            <?php echo htmlspecialchars($log['event_type']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($log['username'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($log['ip_address'] ?? 'N/A'); ?></td>
                                    <td>
                                        <span class="log-details" title="<?php echo htmlspecialchars($log['details']); ?>">
                                            <?php echo htmlspecialchars($log['details']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <nav>
                        <ul class="pagination">
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; 
                                        echo !empty($event_type) ? "&event_type=" . urlencode($event_type) : '';
                                        echo !empty($username) ? "&username=" . urlencode($username) : '';
                                        echo !empty($start_date) ? "&start_date=" . urlencode($start_date) : '';
                                        echo !empty($end_date) ? "&end_date=" . urlencode($end_date) : '';
                                    ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('.select2').select2({
        placeholder: 'Select an option',
        allowClear: true
    });
});
</script>
</body>
</html>

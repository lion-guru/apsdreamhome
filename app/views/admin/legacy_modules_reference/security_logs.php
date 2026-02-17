<?php
require_once 'core/init.php';

// Check permissions
if (!isAdmin()) {
    header("Location: index.php?error=access_denied");
    exit();
}

$db = \App\Core\App::database();

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
$where = "WHERE 1=1";
$params = [];

if (!empty($event_type)) {
    $where .= " AND event_type = ?";
    $params[] = $event_type;
}

if (!empty($username)) {
    $where .= " AND username = ?";
    $params[] = $username;
}

if (!empty($start_date)) {
    $where .= " AND created_at >= ?";
    $params[] = $start_date . ' 00:00:00';
}

if (!empty($end_date)) {
    $where .= " AND created_at <= ?";
    $params[] = $end_date . ' 23:59:59';
}

// Fetch logs
$query_with_limit = "SELECT * FROM security_logs $where ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$logs = $db->fetchAll($query_with_limit, $params);

// Get total count for pagination
$countQuery = "SELECT COUNT(*) as count FROM security_logs $where";
$totalLogsData = $db->fetchOne($countQuery, $params);
$totalLogs = $totalLogsData['count'];
$totalPages = ceil($totalLogs / $limit);

// Get unique event types and usernames for filters
$eventTypes = $db->fetchAll("SELECT DISTINCT event_type FROM security_logs ORDER BY event_type");
$usernames = $db->fetchAll("SELECT DISTINCT username FROM security_logs WHERE username IS NOT NULL ORDER BY username");

$page_title = "Security Logs";
include 'admin_header.php';
include 'admin_sidebar.php';
?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Breadcrumb -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Security Logs</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Security Logs</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0 fw-bold">
                    <i class="fas fa-shield-alt me-2 text-primary"></i>
                    Filter Logs
                </h5>
            </div>
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Event Type</label>
                        <select name="event_type" class="form-select select2 border-0 bg-light">
                            <option value="">All Event Types</option>
                            <?php foreach ($eventTypes as $type): ?>
                                <option value="<?php echo h($type['event_type']); ?>" 
                                    <?php echo $event_type === $type['event_type'] ? 'selected' : ''; ?>>
                                    <?php echo h($type['event_type']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Username</label>
                        <select name="username" class="form-select select2 border-0 bg-light">
                            <option value="">All Users</option>
                            <?php foreach ($usernames as $user): ?>
                                <option value="<?php echo h($user['username']); ?>" 
                                    <?php echo $username === $user['username'] ? 'selected' : ''; ?>>
                                    <?php echo h($user['username']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Start Date</label>
                        <input type="date" name="start_date" class="form-control border-0 bg-light" 
                               value="<?php echo h($start_date); ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold">End Date</label>
                        <input type="date" name="end_date" class="form-control border-0 bg-light" 
                               value="<?php echo h($end_date); ?>">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <div class="w-100 d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1">
                                <i class="fas fa-filter me-1"></i> Filter
                            </button>
                            <a href="security_logs.php" class="btn btn-secondary">
                                <i class="fas fa-undo"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0 fw-bold">
                    <i class="fas fa-list me-2 text-primary"></i>
                    System Security Events
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="border-0">Timestamp</th>
                                <th class="border-0">Event Type</th>
                                <th class="border-0">Username</th>
                                <th class="border-0">IP Address</th>
                                <th class="border-0">Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                            <tr>
                                <td class="text-nowrap"><?php echo h($log['created_at']); ?></td>
                                <td>
                                    <?php
                                    $badgeClass = 'info';
                                    $eventType = strtolower($log['event_type']);
                                    if (strpos($eventType, 'failed') !== false) $badgeClass = 'danger';
                                    if (strpos($eventType, 'success') !== false) $badgeClass = 'success';
                                    if (strpos($eventType, 'alert') !== false) $badgeClass = 'warning';
                                    ?>
                                    <span class="badge bg-<?php echo $badgeClass; ?> shadow-sm">
                                        <?php echo h($log['event_type']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <i class="fas fa-user me-1 text-muted"></i>
                                        <?php echo h($log['username'] ?? 'N/A'); ?>
                                    </span>
                                </td>
                                <td>
                                    <code class="text-primary fw-bold"><?php echo h($log['ip_address'] ?? 'N/A'); ?></code>
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 400px;" title="<?php echo h($log['details']); ?>">
                                        <?php echo h($log['details']); ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($logs)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="fas fa-search fa-3x mb-3 d-block opacity-25"></i>
                                        No security logs found.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if ($totalPages > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php 
                        $queryParams = $_GET;
                        for ($i = 1; $i <= $totalPages; $i++): 
                            $queryParams['page'] = $i;
                            $url = '?' . http_build_query($queryParams);
                        ?>
                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                <a class="page-link shadow-sm" href="<?php echo h($url); ?>">
                                    <?php echo h($i); ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.select2').select2({
        width: '100%',
        dropdownParent: $('.card-body')
    });
});
</script>

<?php require_once 'includes/admin_footer.php'; ?>



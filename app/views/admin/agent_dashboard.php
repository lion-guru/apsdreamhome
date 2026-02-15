<?php
// Agent Dashboard
require_once __DIR__ . '/core/init.php';
require_once __DIR__ . '/../includes/performance_manager.php';

$userRole = getAuthRole();
$userSubRole = getAuthSubRole();
$allowedRoles = ['agent', 'admin', 'superadmin', 'super_admin'];

if (!in_array($userRole, $allowedRoles) && !in_array($userSubRole, $allowedRoles)) {
    header('Location: login.php?error=unauthorized');
    exit();
}

$agent_id = getAuthUserId();
$agent_name = getAuthFullName() ?: (getAuthUsername() ?? 'Agent');

$perfManager = getPerformanceManager();

// Fetch agent-specific stats with caching
$stats = [
    'my_leads' => $perfManager->getCachedQuery("SELECT COUNT(*) FROM leads WHERE assigned_to = ?", [$agent_id], 300),
    'my_properties' => $perfManager->getCachedQuery("SELECT COUNT(*) FROM properties WHERE agent_id = ?", [$agent_id], 3600),
    'upcoming_visits' => $perfManager->getCachedQuery("SELECT COUNT(*) FROM site_visits WHERE agent_id = ? AND visit_date >= CURDATE()", [$agent_id], 300),
    'closed_deals' => $perfManager->getCachedQuery("SELECT COUNT(*) FROM bookings WHERE agent_id = ? AND status='completed'", [$agent_id], 86400),
];

// Helper to get scalar value from query result
foreach ($stats as $key => $value) {
    if (is_array($value) && !empty($value)) {
        $stats[$key] = array_values($value[0])[0];
    } else {
        $stats[$key] = 0;
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Agent Dashboard</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
</head>
<body>
    <div class="container py-5">
        <div class="d-flex justify-content-end mb-3">
            <a href="logout.php" class="btn btn-outline-danger btn-sm"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
        <h1 class="mb-4 text-center text-success">Welcome, <?php echo h($agent_name); ?>!</h1>
        <p class="lead text-center">This is your Agent Dashboard. Here you can manage your properties, leads, and visits.</p>
        
        <!-- Agent Stats Cards -->
        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <div class="card bg-primary text-white h-100 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-users fa-3x mb-3"></i>
                        <h5 class="card-title">My Leads</h5>
                        <p class="card-text fs-2 fw-bold"><?php echo $stats['my_leads']; ?></p>
                        <a href="leads.php" class="btn btn-light btn-sm mt-2">View Leads</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white h-100 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-building fa-3x mb-3"></i>
                        <h5 class="card-title">My Properties</h5>
                        <p class="card-text fs-2 fw-bold"><?php echo $stats['my_properties']; ?></p>
                        <a href="properties.php" class="btn btn-light btn-sm mt-2">View Properties</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white h-100 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-calendar-check fa-3x mb-3"></i>
                        <h5 class="card-title">Upcoming Visits</h5>
                        <p class="card-text fs-2 fw-bold"><?php echo $stats['upcoming_visits']; ?></p>
                        <a href="site_visits.php" class="btn btn-light btn-sm mt-2">View Visits</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-dark h-100 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-handshake fa-3x mb-3"></i>
                        <h5 class="card-title">Closed Deals</h5>
                        <p class="card-text fs-2 fw-bold"><?php echo $stats['closed_deals']; ?></p>
                        <a href="bookings.php" class="btn btn-outline-dark btn-sm mt-2">View Deals</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-tasks me-2"></i>Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <a href="add_lead.php" class="list-group-item list-group-item-action"><i class="fas fa-plus-circle me-2"></i>Add New Lead</a>
                            <a href="add_property.php" class="list-group-item list-group-item-action"><i class="fas fa-plus-square me-2"></i>Add New Property</a>
                            <a href="schedule_visit.php" class="list-group-item list-group-item-action"><i class="fas fa-calendar-plus me-2"></i>Schedule Site Visit</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>System Announcements</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-0">No new announcements at this time.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>
